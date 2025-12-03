<?php
// admin/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// VK ID
const VK_CLIENT_ID     = 54372076; // ID приложения
const VK_CLIENT_SECRET = 'JARMXBJkFx8GZeoGjNZu';
const VK_REDIRECT_URI  = 'https://unencountered-articulately-candyce.ngrok-free.dev/admin/vk_callback.php';
const VK_API_VERSION   = '5.199';







