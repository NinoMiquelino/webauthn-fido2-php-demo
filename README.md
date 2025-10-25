## 👨‍💻 Autor

<div align="center">
  <img src="https://avatars.githubusercontent.com/ninomiquelino" width="100" height="100" style="border-radius: 50%">
  <br>
  <strong>Onivaldo Miquelino</strong>
  <br>
  <a href="https://github.com/ninomiquelino">@ninomiquelino</a>
</div>

---

# 🧭 Demonstração do Fluxo FIDO2 (Passkeys) com PHP

![Made with PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![Frontend JavaScript](https://img.shields.io/badge/Frontend-JavaScript-F7DF1E?logo=javascript&logoColor=black)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-38B2AC?logo=tailwindcss&logoColor=white)
![License MIT](https://img.shields.io/badge/License-MIT-green)
![Status Stable](https://img.shields.io/badge/Status-Stable-success)
![Version 1.0.0](https://img.shields.io/badge/Version-1.0.0-blue)
![GitHub stars](https://img.shields.io/github/stars/NinoMiquelino/password-strength-checker?style=social)
![GitHub forks](https://img.shields.io/github/forks/NinoMiquelino/password-strength-checker?style=social)
![GitHub issues](https://img.shields.io/github/issues/NinoMiquelino/password-strength-checker)

---

## 💻 Visão Geral do Projeto

Este projeto é uma demonstração didática e prática do fluxo de **Autenticação Sem Senha (Passkeys ou Chaves de Acesso)** utilizando o padrão **WebAuthn (FIDO2)**.

O objetivo principal é ilustrar o processo completo de **Registro e Login criptográfico**, mostrando a interação entre o navegador (cliente), a API WebAuthn e o servidor PHP.

⚠️ **Requisito de Segurança:** Devido às exigências da API WebAuthn, esta demonstração só pode ser executada em ambiente **HTTPS** (produção) ou em `http://localhost` (desenvolvimento).

---

## ⚙️ Funcionalidades
- 🪪 **Fluxo Completo de Registro (Attestation):** Demonstra a criação de uma credencial FIDO2 e o envio da chave pública para o servidor.  
- 🔐 **Fluxo Completo de Login (Assertion):** Demonstra a prova de posse da chave privada usando biometria ou PIN do dispositivo.  
- 🧠 **Backend em PHP:** Implementação do lado do servidor que gerencia os desafios criptográficos e a "simulação" da verificação de assinatura e credenciais.  
- 💾 **Armazenamento em Sessão PHP:** As credenciais e dados do usuário são armazenados de forma não persistente na Sessão PHP (`$_SESSION`) para fins estritamente demonstrativos.  
- 🛡️ **Segurança e Modernidade:** Substitui senhas por credenciais criptográficas de chave pública — a principal defesa contra phishing.

---

## 🧩 Estrutura do Projeto
```
webauthn-fido2-php-demo/
📁 docs/
│   ├── index.html
│   └── auth_server.php
│   └── assets/
│       ├── css/style.css
│       └── js/script.js
├── README.md
├── .gitignore
└── LICENSE
```
---

## 🚀 Visualizar na prática

### 🔸 Frontend (JavaScript)
👉 [**Acesse o site online**](https://ninomiquelino.github.io/webauthn-fido2-php-demo/)  
Digite o nome de usuário e clique em Registrar Nova Chave de Acessoa e veja o resultado instantaneamente na interface.

---

## 🧠 Tecnologias utilizadas
- 💻 HTML5 / CSS3
- 🎨 Tailwind CSS
- ⚡ JavaScript (ES6+)
- 🐘 PHP 8.3+

---

## 📦 Como usar
1. Clone este repositório:
   ```bash
   git clone https://github.com/ninomiquelino/webauthn-fido2-php-demo.git

---   

## 🧾 Licença
Distribuído sob a licença **MIT**.  
Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## 🤝 Contribuições
Contribuições são sempre bem-vindas!  
Sinta-se à vontade para abrir uma [*issue*](https://github.com/NinoMiquelino/webauthn-fido2-php-demo/issues) com sugestões ou enviar um [*pull request*](https://github.com/NinoMiquelino/webauthn-fido2-php-demo/pulls) com melhorias.

---

## 💬 Contato
📧 [Entre em contato pelo LinkedIn](https://www.linkedin.com/in/onivaldomiquelino/)  
💻 Desenvolvido por **Onivaldo Miquelino**

---

### 🏷️ Explicando os badges:
| Badge | Significado |
|--------|--------------|
| 🟣 **Made with PHP** | Indica a principal linguagem usada no backend |
| 🟡 **Frontend JavaScript** | Mostra a stack usada na interface |
| 🟦 **TailwindCSS** | Framework CSS usado para estilização moderna e responsiva |
| 🟢 **License MIT** | Mostra a licença do repositório |
| 💙 **Version 1.0.0** | Versão estável do projeto |
| ✅ **Status Stable** | Mostra que o projeto está funcionando corretamente |

---
