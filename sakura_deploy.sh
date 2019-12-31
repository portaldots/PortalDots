#!/bin/bash

# さくらのレンタルサーバーにデプロイするためのシェルスクリプト
#
# 〔CircleCI に設定する環境変数について〕
#
# 【デプロイに関する設定】
# ※ SSH接続するための秘密鍵は別途CircleCIに登録しておく
# SSH_HOST=(さくらのレンサバのSSHホスト・スタンダードプランなら ユーザーネーム.sakura.ne.jp )
# SSH_USERNAME=(さくらのレンサバのユーザーネーム)
# DEPLOY_DIRECTORY=(デプロイ先ディレクトリの名前)
# - /home/$SSH_USERNAME/$DEPLOY_DIRECTORY/ にアプリケーションがデプロイされる
# - /home/$SSH_USERNAME/www/$DEPLOY_DIRECTORY/ に index.php やアセットなどがデプロイされる
#    → レンサバコントロールパネルでは、このディレクトリをドメインと紐付ける
#
# 【ポータルに関する設定】
# APP_NAME=(ポータルの名称)
# APP_KEY=(ローカル環境で php artisan key:generate --show を実行した時に表示される文字列)
# APP_URL=(ポータルのURL)
# PORTAL_ADMIN_NAME=(ポータルの運営組織名)
# PORTAL_CONTACT_EMAIL=(運営組織への連絡メールアドレス)
# PORTAL_UNIVEMAIL_DOMAIN=(学生用大学発行メールアドレスのドメイン・理科大なら ed.tus.ac.jp )
# PORTAL_UPLOAD_DIR_CRUD=(通常は ../../デプロイ先ディレクトリの名前/application/uploads)
#
# 【データベースに関する設定】
# DB_HOST=(DBのホスト名)
# DB_PORT=(DBのポート番号・3306 のような数字)
# DB_DATABASE=(DBの名前)
# DB_USERNAME=(DBのユーザー名)
# DB_PASSWORD=(DBのパスワード)
#
# 【メールサーバーに関する設定】
# MAIL_HOST=(メールのホスト名)
# MAIL_PORT=(メールのポート番号・587 のような数字)
# MAIL_USERNAME=(メールのユーザー名)
# MAIL_PASSWORD=(メールのパスワード)
# MAIL_FROM_ADDRESS=(ポータルから送信されるメールの差出人)
# MAIL_FROM_NAME=(ユーザーフレンドリーな差出人名)

echo "【デプロイスクリプト Start】"

rm -rf dist/

# 全ファイルを dist へ移動させる
rsync -av --update --delete --stats ./ ./dist/ --exclude='dist/' --exclude='docker_dev/' --exclude='.git/' --exclude='/vendor/' --exclude='node_modules/' >& /dev/null

cd dist/; composer install --optimize-autoloader --no-dev; yarn install; cd ../

cd dist/; yarn run production; cd ../

php -r "copy('dist/.env.prod', 'dist/.env');" >& /dev/null

# .env ファイルを CircleCI の環境変数を元に作成する
echo ".env ファイルの作成 Start"
cd dist/
yarn replace "%APP_NAME%" "${APP_NAME}" .env >& /dev/null
yarn replace "%APP_KEY%" "${APP_KEY}" .env >& /dev/null
yarn replace "%APP_URL%" "${APP_URL}" .env >& /dev/null
yarn replace "%PORTAL_ADMIN_NAME%" "${PORTAL_ADMIN_NAME}" .env >& /dev/null
yarn replace "%PORTAL_CONTACT_EMAIL%" "${PORTAL_CONTACT_EMAIL}" .env >& /dev/null
yarn replace "%PORTAL_UNIVEMAIL_DOMAIN%" "${PORTAL_UNIVEMAIL_DOMAIN}" .env >& /dev/null
yarn replace "%DB_HOST%" "${DB_HOST}" .env >& /dev/null
yarn replace "%DB_PORT%" "${DB_PORT}" .env >& /dev/null
yarn replace "%DB_DATABASE%" "${DB_DATABASE}" .env >& /dev/null
yarn replace "%DB_USERNAME%" "${DB_USERNAME}" .env >& /dev/null
yarn replace "%DB_PASSWORD%" "${DB_PASSWORD}" .env >& /dev/null
yarn replace "%MAIL_HOST%" "${MAIL_HOST}" .env >& /dev/null
yarn replace "%MAIL_PORT%" "${MAIL_PORT}" .env >& /dev/null
yarn replace "%MAIL_USERNAME%" "${MAIL_USERNAME}" .env >& /dev/null
yarn replace "%MAIL_PASSWORD%" "${MAIL_PASSWORD}" .env >& /dev/null
yarn replace "%MAIL_FROM_ADDRESS%" "${MAIL_FROM_ADDRESS}" .env >& /dev/null
yarn replace "%MAIL_FROM_NAME%" "${MAIL_FROM_NAME}" .env >& /dev/null
# ↓この行は、Grocery CRUD を完全廃止したら削除する
yarn replace "%PORTAL_UPLOAD_DIR_CRUD%" "${PORTAL_UPLOAD_DIR_CRUD}" .env >& /dev/null
cd ../
echo ".env ファイルの作成 End"

echo "パスの修正 Start"
cd dist/
yarn replace "/../" "/../../${DEPLOY_DIRECTORY}/" public/index.php >& /dev/null
yarn replace "/../" "/../../${DEPLOY_DIRECTORY}/" public/index_laravel.php >& /dev/null
cd ../
echo "パスの修正 End"

echo "メンテナンスモード On"
ssh ${SSH_USERNAME}@${SSH_HOST} -o StrictHostKeyChecking=no <<EOC
cd /home/${SSH_USERNAME}/${DEPLOY_DIRECTORY}/
if [ -f artisan ]; then
    php artisan down --message=メンテナンス中です
fi
EOC
echo "メンテナンスモード On 完了"

echo "デプロイ Start"
rsync -avz --update -e "ssh -o StrictHostKeyChecking=no" ./dist/ "${SSH_USERNAME}@${SSH_HOST}:/home/${SSH_USERNAME}/${DEPLOY_DIRECTORY}/" >& /dev/null

rsync -avz --update -e "ssh -o StrictHostKeyChecking=no" ./dist/public/ "${SSH_USERNAME}@${SSH_HOST}:/home/${SSH_USERNAME}/www/${DEPLOY_DIRECTORY}/" >& /dev/null

ssh ${SSH_USERNAME}@${SSH_HOST} -o StrictHostKeyChecking=no "cd /home/${SSH_USERNAME}/${DEPLOY_DIRECTORY}/; php artisan config:cache; php artisan route:cache; php artisan view:clear; php artisan migrate --force" >& /dev/null
echo "デプロイ End"

echo "メンテナンスモード解除"
ssh ${SSH_USERNAME}@${SSH_HOST} -o StrictHostKeyChecking=no "cd /home/${SSH_USERNAME}/${DEPLOY_DIRECTORY}/; php artisan up" >& /dev/null
echo "メンテナンスモード解除完了"

echo "【デプロイスクリプト End】"
