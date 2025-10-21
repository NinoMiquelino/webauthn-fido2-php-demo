        // Variáveis de ambiente para o endpoint (Assumindo que o PHP está no mesmo diretório)
        const SERVER_URL = 'auth_server.php';
        
        const usernameInput = document.getElementById('username');
        const statusMessage = document.getElementById('status-message');
        const registerBtn = document.getElementById('register-btn');
        const loginBtn = document.getElementById('login-btn');

        /**
         * Exibe uma mensagem de status/erro na UI.
         * @param {string} message A mensagem a ser exibida.
         * @param {string} type O tipo de mensagem ('success', 'error', 'info').
         */
        function displayMessage(message, type = 'info') {
            statusMessage.textContent = message;
            statusMessage.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700', 'bg-blue-100', 'text-blue-700');
            statusMessage.classList.add('block');
            
            if (type === 'success') {
                statusMessage.classList.add('bg-green-100', 'text-green-700');
            } else if (type === 'error') {
                statusMessage.classList.add('bg-red-100', 'text-red-700');
            } else {
                statusMessage.classList.add('bg-blue-100', 'text-blue-700');
            }
        }

        /**
         * Codifica um ArrayBuffer para Base64 URL-safe.
         * @param {ArrayBuffer} buffer 
         * @returns {string}
         */
        function arrayBufferToBase64Url(buffer) {
            const bytes = new Uint8Array(buffer);
            let binary = '';
            bytes.forEach(b => binary += String.fromCharCode(b));
            return btoa(binary)
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=/g, '');
        }

        /**
         * Decodifica uma string Base64 URL-safe para ArrayBuffer.
         * @param {string} base64UrlString 
         * @returns {ArrayBuffer}
         */
        function base64UrlToArrayBuffer(base64UrlString) {
            let base64 = base64UrlString
                .replace(/-/g, '+')
                .replace(/_/g, '/');
            
            while (base64.length % 4) {
                base64 += '=';
            }

            const raw = atob(base64);
            return Uint8Array.from(raw, c => c.charCodeAt(0)).buffer;
        }

        /**
         * Inicia o fluxo de registro de uma nova credencial WebAuthn.
         */
        async function startRegistration() {
            const username = usernameInput.value.trim();
            if (!username) {
                displayMessage('Por favor, insira um nome de usuário (email).', 'error');
                return;
            }

            displayMessage('Iniciando registro...', 'info');
            registerBtn.disabled = true;
            loginBtn.disabled = true;

            try {
                // 1. Solicita as opções de criação ao servidor
                const response = await fetch(SERVER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register_start', username })
                });

                const options = await response.json();
                if (options.status !== 'ok') {
                    throw new Error(options.message || 'Erro ao obter opções de registro.');
                }
                
                // Converte Base64Url para ArrayBuffer
                options.publicKey.challenge = base64UrlToArrayBuffer(options.publicKey.challenge);
                options.publicKey.user.id = base64UrlToArrayBuffer(options.publicKey.user.id);
                if (options.publicKey.excludeCredentials) {
                    options.publicKey.excludeCredentials.forEach(cred => {
                        cred.id = base64UrlToArrayBuffer(cred.id);
                    });
                }
                
                // 2. Chama a API do navegador para criar a credencial
                const attestation = await navigator.credentials.create({
                    publicKey: options.publicKey
                });

                // 3. Formata a resposta para o servidor
                const responseData = {
                    id: attestation.id,
                    rawId: arrayBufferToBase64Url(attestation.rawId),
                    type: attestation.type,
                    response: {
                        clientDataJSON: arrayBufferToBase64Url(attestation.response.clientDataJSON),
                        attestationObject: arrayBufferToBase64Url(attestation.response.attestationObject)
                    }
                };
                
                // 4. Envia a credencial para o servidor salvar
                const verificationResponse = await fetch(SERVER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register_finish', attestation: responseData, username })
                });

                const result = await verificationResponse.json();
                if (result.status === 'ok') {
                    displayMessage('✅ Registro da Chave de Acesso realizado com sucesso!', 'success');
                } else {
                    displayMessage(`❌ Erro no servidor: ${result.message}`, 'error');
                }

            } catch (error) {
                console.error("Erro de Registro:", error);
                displayMessage(`❌ Falha no registro da Chave de Acesso: ${error.name || error.message}`, 'error');
            } finally {
                registerBtn.disabled = false;
                loginBtn.disabled = false;
            }
        }

        /**
         * Inicia o fluxo de autenticação (login) WebAuthn.
         */
        async function startAuthentication() {
            const username = usernameInput.value.trim();
            if (!username) {
                displayMessage('Por favor, insira um nome de usuário (email) para login.', 'error');
                return;
            }

            displayMessage('Iniciando login...', 'info');
            registerBtn.disabled = true;
            loginBtn.disabled = true;

            try {
                // 1. Solicita as opções de autenticação ao servidor
                const response = await fetch(SERVER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login_start', username })
                });

                const options = await response.json();
                if (options.status !== 'ok') {
                    throw new Error(options.message || 'Erro ao obter opções de login.');
                }

                // Converte Base64Url para ArrayBuffer
                options.publicKey.challenge = base64UrlToArrayBuffer(options.publicKey.challenge);
                if (options.publicKey.allowCredentials) {
                    options.publicKey.allowCredentials.forEach(cred => {
                        cred.id = base64UrlToArrayBuffer(cred.id);
                    });
                }

                // 2. Chama a API do navegador para obter a asserção
                const assertion = await navigator.credentials.get({
                    publicKey: options.publicKey
                });

                // 3. Formata a resposta para o servidor
                const responseData = {
                    id: assertion.id,
                    rawId: arrayBufferToBase64Url(assertion.rawId),
                    type: assertion.type,
                    response: {
                        clientDataJSON: arrayBufferToBase64Url(assertion.response.clientDataJSON),
                        authenticatorData: arrayBufferToBase64Url(assertion.response.authenticatorData),
                        signature: arrayBufferToBase64Url(assertion.response.signature),
                        userHandle: assertion.response.userHandle ? arrayBufferToBase64Url(assertion.response.userHandle) : null
                    }
                };
                
                // 4. Envia a asserção para o servidor verificar
                const verificationResponse = await fetch(SERVER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login_finish', assertion: responseData, username })
                });

                const result = await verificationResponse.json();
                if (result.status === 'ok') {
                    displayMessage('🎉 Login com Chave de Acesso SUCESSO! Você está autenticado.', 'success');
                } else {
                    displayMessage(`❌ Falha na autenticação: ${result.message}`, 'error');
                }

            } catch (error) {
                console.error("Erro de Login:", error);
                displayMessage(`❌ Falha no login com Chave de Acesso: ${error.name || error.message}`, 'error');
            } finally {
                registerBtn.disabled = false;
                loginBtn.disabled = false;
            }
        }

        // Verifica o suporte ao WebAuthn
        window.onload = () => {
            if (!window.PublicKeyCredential) {
                displayMessage("Seu navegador não suporta a API WebAuthn (Chaves de Acesso). Tente Chrome, Edge ou Firefox.", 'error');
                registerBtn.disabled = true;
                loginBtn.disabled = true;
            }
        };