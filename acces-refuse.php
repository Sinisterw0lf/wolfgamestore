<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
echo "Accès refusé. Vous n'avez pas les permissions nécessaires.";
?>