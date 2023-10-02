# API REST JWT BOOK API

### 1. Requirements
1. Composer
2. PHP
3. Insomnia or Postman

### 2. Installation
1. Create a Virtual Host
2. Configurate the ".env" file so that it corresponds to your environnement (I used WAMPServer)
3. Install dependencies : ```composer install```
4. Create the database : ```php bin/console doctrine:database:create```
5. Update the database structure : ```php bin/console doctrine:schema:update --force```
6. Upload the data fixtures to the database : ```php bin/console doctrine:fixtures:load```
7. Generate a private key for the JWT* : ```openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096```
8. Generate a public key for the JWT* : ```openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout```
9. Replace JWT_PASSPHRASE value in the ".env" file by the password you just entered for the generated keys


*You can execute these commands in git bash terminal if you don't have openssl installed

### 3. Usage/Access
Link to documentation : ```{your-virtual-host}/api/doc```