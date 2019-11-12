echo --------------------------------------------------------------------------
echo ---------------Git pull--------------------------------------------------
echo --------------------------------------------------------------------------
git add .
git stash
git fetch --tags

# Get latest tag name
latestTag=$(git tag | grep "^[0-9]\+\.[0-9]\+\.[0-9]\+$" | sort -t. -k 1,1n -k 2,2n -k 3,3n | tail -1)
echo Neueste Version: $latestTag
# Checkout latest tag
git checkout $latestTag

echo ------------Update parameter.yml and install latest packages-------------
php composer.phar install
echo --------------------------------------------------------------------------
echo ----------------Update Database------------------------------------------
echo --------------------------------------------------------------------------
php bin/console doctrine:schema:update --force
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
chmod -R 775 public/uploads/image
echo --------------------------------------------------------------------------
echo -----------------------Restart Apache-------------------------------------
echo --------------------------------------------------------------------------
service apache2 restart
