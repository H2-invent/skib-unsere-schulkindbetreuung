
echo --------------------------------------------------------------------------
echo ---------------Git pull--------------------------------------------------
echo --------------------------------------------------------------------------
git add .
git stash
git pull

echo ------------Update parameter.yml and install latest packages-------------
php composer.phar install
echo --------------------------------------------------------------------------
echo ----------------Update Database------------------------------------------
echo --------------------------------------------------------------------------
php bin/console cache:clear
php bin/console doctrine:migrations:status
php bin/console doctrine:migration:migrate
echo --------------------------------------------------------------------------
echo -----------------Clear Cache----------------------------------------------
echo --------------------------------------------------------------------------
php bin/console cache:clear
php bin/console cache:warmup
echo --------------------------------------------------------------------------
echo ----------------Setting Permissin------------------------------------------
echo --------------------------------------------------------------------------
chown -R www-data:www-data var/cache
chmod -R 775 var/cache
echo --------------------------------------------------------------------------
echo ----------------Create Upload Folder and Set permissions------------------
echo --------------------------------------------------------------------------
mkdir public/uploads
mkdir public/uploads/images
chown -R www-data:www-data public/uploads/images
chmod -R 775 public/uploads/images
echo --------------------------------------------------------------------------
echo -----------------------Install NPM and Assets----------------------------
echo --------------------------------------------------------------------------
npm install
npm run build
echo --------------------------------------------------------------------------
echo -----------------Security Check----------------------------------------------
echo --------------------------------------------------------------------------
php security-checker.phar security:check composer.lock


