CREATE DATABASE suivi_colis_iutv;
CREATE USER 'app_colis'@'localhost' IDENTIFIED BY "password";
GRANT ALL PRIVILEGES ON suivi_colis_iutv.* TO 'app_colis'@'localhost';
FLUSH PRIVILEGES;