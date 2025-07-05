<?php
// Configurações seguras para a sessão
ini_set('session.cookie_httponly', 1); // Torna o cookie acessível apenas via HTTP, impedindo acesso por JavaScript
ini_set('session.cookie_secure', 1);   // Garante que o cookie seja enviado apenas por HTTPS (necessário HTTPS no servidor)
ini_set('session.use_strict_mode', 1); // Evita que sessões sejam assumidas
?>