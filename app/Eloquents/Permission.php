<?php

namespace App\Eloquents;

use App\Eloquents\ValueObjects\PermissionInfo;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * モデルの配列形態に追加するアクセサ
     *
     * @var array
     */
    protected $appends = ['display_name'];

    public function getDisplayNameAttribute(): string
    {
        return $this->getDefinedPermissions()[$this->name]->getDisplayName();
    }

    public static function getDefinedPermissions()
    {
        static $defined_permissions = null;

        if (empty($defined_permissions)) {
            $defined_permissions = [
                'staff.users' => new PermissionInfo(
                    'staff.users',
                    'スタッフモード › ユーザー情報管理 › 全機能',
                    'ユーザー(全機能)',
                    'ユーザー情報管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.users.read,export' => new PermissionInfo(
                    'staff.users.read,export',
                    'スタッフモード › ユーザー情報管理 › 閲覧とCSVエクスポート',
                    'ユーザー(エクスポート)',
                    'ユーザーの閲覧とCSVエクスポートが可能'
                ),
                'staff.users.read,edit' => new PermissionInfo(
                    'staff.users.read,edit',
                    'スタッフモード › ユーザー情報管理 › 閲覧と編集',
                    'ユーザー(編集)',
                    'ユーザーの閲覧と編集が可能'
                ),
                'staff.users.read' => new PermissionInfo(
                    'staff.users.read',
                    'スタッフモード › ユーザー情報管理 › 閲覧',
                    'ユーザー(閲覧)',
                    'ユーザーの閲覧が可能'
                ),
                'staff.circles' => new PermissionInfo(
                    'staff.circles',
                    'スタッフモード › 企画情報管理 › 全機能',
                    '企画(全機能)',
                    '企画情報管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.circles.custom_form' => new PermissionInfo(
                    'staff.circles.participation_types',
                    'スタッフモード › 企画情報管理 › 参加種別／企画参加登録の編集',
                    '企画(参加種別)',
                    '参加種別の編集が可能。ただし、企画参加登録フォームを編集したい場合、別途フォームの編集権限が必要です。'
                ),
                'staff.circles.read,edit,delete' => new PermissionInfo(
                    'staff.circles.read,edit,delete',
                    'スタッフモード › 企画情報管理 › 閲覧と編集、削除',
                    '企画(削除)',
                    '企画の閲覧と編集、作成、削除が可能'
                ),
                'staff.circles.read,edit' => new PermissionInfo(
                    'staff.circles.read,edit',
                    'スタッフモード › 企画情報管理 › 閲覧と編集',
                    '企画(編集)',
                    '企画の閲覧と編集、作成が可能'
                ),
                'staff.circles.read,send_email' => new PermissionInfo(
                    'staff.circles.read,send_email',
                    'スタッフモード › 企画情報管理 › 閲覧と「企画へメール送信」',
                    '企画(メール送信)',
                    '企画の閲覧、および「企画へメール送信」機能が利用可能'
                ),
                'staff.circles.read,export' => new PermissionInfo(
                    'staff.circles.read,export',
                    'スタッフモード › 企画情報管理 › 閲覧とCSVエクスポート',
                    '企画(エクスポート)',
                    '企画の閲覧とCSVエクスポートが可能'
                ),
                'staff.circles.read' => new PermissionInfo(
                    'staff.circles.read',
                    'スタッフモード › 企画情報管理 › 閲覧',
                    '企画(閲覧)',
                    '企画の閲覧が可能'
                ),
                'staff.tags' => new PermissionInfo(
                    'staff.tags',
                    'スタッフモード › 企画タグ管理 › 全機能',
                    '企画タグ(全機能)',
                    '企画タグ管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.tags.read,edit,delete' => new PermissionInfo(
                    'staff.tags.read,edit,delete',
                    'スタッフモード › 企画タグ管理 › 閲覧と編集、削除',
                    '企画タグ(削除)',
                    '企画タグの閲覧と編集、作成、削除が可能'
                ),
                'staff.tags.read,edit' => new PermissionInfo(
                    'staff.tags.read,edit',
                    'スタッフモード › 企画タグ管理 › 閲覧と編集',
                    '企画タグ(編集)',
                    '企画タグの閲覧と編集、作成が可能'
                ),
                'staff.tags.read,export' => new PermissionInfo(
                    'staff.tags.read,export',
                    'スタッフモード › 企画タグ管理 › 閲覧とCSVエクスポート',
                    '企画タグ(エクスポート)',
                    '企画タグの閲覧とCSVエクスポートが可能'
                ),
                'staff.tags.read' => new PermissionInfo(
                    'staff.tags.read',
                    'スタッフモード › 企画タグ管理 › 閲覧',
                    '企画タグ(閲覧)',
                    '企画タグの閲覧が可能'
                ),
                'staff.places' => new PermissionInfo(
                    'staff.places',
                    'スタッフモード › 場所情報管理 › 全機能',
                    '場所(全機能)',
                    '場所情報管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.places.read,edit,delete' => new PermissionInfo(
                    'staff.places.read,edit,delete',
                    'スタッフモード › 場所情報管理 › 閲覧と編集、削除',
                    '場所(削除)',
                    '場所の閲覧と編集、作成、削除が可能'
                ),
                'staff.places.read,edit' => new PermissionInfo(
                    'staff.places.read,edit',
                    'スタッフモード › 場所情報管理 › 閲覧と編集',
                    '場所(編集)',
                    '場所の閲覧と編集、作成が可能'
                ),
                'staff.places.read,export' => new PermissionInfo(
                    'staff.places.read,export',
                    'スタッフモード › 場所情報管理 › 閲覧とCSVエクスポート',
                    '場所(エクスポート)',
                    '場所の閲覧とCSVエクスポートが可能'
                ),
                'staff.places.read' => new PermissionInfo(
                    'staff.places.read',
                    'スタッフモード › 場所情報管理 › 閲覧',
                    '場所(閲覧)',
                    '場所の閲覧が可能'
                ),
                'staff.pages' => new PermissionInfo(
                    'staff.pages',
                    'スタッフモード › お知らせ管理 › 全機能',
                    'お知らせ(全機能)',
                    'お知らせ管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.pages.read,edit,delete' => new PermissionInfo(
                    'staff.pages.read,edit,delete',
                    'スタッフモード › お知らせ管理 › 閲覧と編集、削除',
                    'お知らせ(削除)',
                    'お知らせの閲覧と編集、作成、削除が可能'
                ),
                'staff.pages.read,edit,send_emails' => new PermissionInfo(
                    'staff.pages.read,edit,send_emails',
                    'スタッフモード › お知らせ管理 › 閲覧と編集、メール配信',
                    'お知らせ(メール配信)',
                    'お知らせの閲覧と編集、作成、メール配信機能の利用が可能'
                ),
                'staff.pages.read,edit' => new PermissionInfo(
                    'staff.pages.read,edit',
                    'スタッフモード › お知らせ管理 › 閲覧と編集',
                    'お知らせ(編集)',
                    'お知らせの閲覧と編集、作成が可能。ただし、お知らせをメールで配信することはできません。'
                ),
                'staff.pages.read,export' => new PermissionInfo(
                    'staff.pages.read,export',
                    'スタッフモード › お知らせ管理 › 閲覧とCSVエクスポート',
                    'お知らせ(エクスポート)',
                    'お知らせの閲覧とCSVエクスポートが可能'
                ),
                'staff.pages.read' => new PermissionInfo(
                    'staff.pages.read',
                    'スタッフモード › お知らせ管理 › 閲覧',
                    'お知らせ(閲覧)',
                    'お知らせの閲覧が可能'
                ),
                'staff.documents' => new PermissionInfo(
                    'staff.documents',
                    'スタッフモード › 配布資料管理 › 全機能',
                    '配布資料(全機能)',
                    '配布資料管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.documents.read,edit,delete' => new PermissionInfo(
                    'staff.documents.read,edit,delete',
                    'スタッフモード › 配布資料管理 › 閲覧と編集、削除',
                    '配布資料(削除)',
                    '配布資料の閲覧と編集、作成、削除が可能'
                ),
                'staff.documents.read,edit' => new PermissionInfo(
                    'staff.documents.read,edit',
                    'スタッフモード › 配布資料管理 › 閲覧と編集',
                    '配布資料(編集)',
                    '配布資料の閲覧と編集、作成が可能'
                ),
                'staff.documents.read,export' => new PermissionInfo(
                    'staff.documents.read,export',
                    'スタッフモード › 配布資料管理 › 閲覧とCSVエクスポート',
                    '配布資料(エクスポート)',
                    '配布資料の閲覧とCSVエクスポートが可能'
                ),
                'staff.documents.read' => new PermissionInfo(
                    'staff.documents.read',
                    'スタッフモード › 配布資料管理 › 閲覧',
                    '配布資料(閲覧)',
                    '配布資料の閲覧が可能'
                ),
                'staff.forms' => new PermissionInfo(
                    'staff.forms',
                    'スタッフモード › 申請管理 › 全機能',
                    '申請(全機能)',
                    '申請管理の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.forms.read,edit,duplicate' => new PermissionInfo(
                    'staff.forms.read,edit,duplicate',
                    'スタッフモード › 申請管理 › フォームの閲覧と編集、複製',
                    '申請(フォームの複製)',
                    'フォームの閲覧と編集、作成、複製が可能。ただし、回答の閲覧・編集はできません。'
                ),
                'staff.forms.read,edit' => new PermissionInfo(
                    'staff.forms.read,edit',
                    'スタッフモード › 申請管理 › フォームの閲覧と編集',
                    '申請(フォームの編集)',
                    'フォームの閲覧と編集、作成が可能'
                ),
                'staff.forms.read,export' => new PermissionInfo(
                    'staff.forms.read,export',
                    'スタッフモード › 申請管理 › フォームの閲覧とCSVエクスポート',
                    '申請(フォームのエクスポート)',
                    'フォームの閲覧とCSVエクスポートが可能。ただし、回答の閲覧・回答のCSVエクスポートはできません。'
                ),
                'staff.forms.read' => new PermissionInfo(
                    'staff.forms.read',
                    'スタッフモード › 申請管理 › フォームの閲覧',
                    '申請(フォームの閲覧)',
                    'フォームの閲覧が可能。ただし、回答の閲覧はできません。'
                ),
                'staff.forms.answers.read,edit' => new PermissionInfo(
                    'staff.forms.answers.read,edit',
                    'スタッフモード › 申請管理 › 回答の閲覧と編集',
                    '申請(回答の編集)',
                    '回答の閲覧と編集、作成が可能'
                ),
                'staff.forms.answers.read,export' => new PermissionInfo(
                    'staff.forms.answers.read,export',
                    'スタッフモード › 申請管理 › 回答の閲覧とCSVエクスポート、「ファイルを一括ダウンロード」',
                    '申請(回答のエクスポート)',
                    '回答の閲覧、CSVエクスポート、アップロードされたファイルの一括ダウンロードする機能が利用可能'
                ),
                'staff.forms.answers.read' => new PermissionInfo(
                    'staff.forms.answers.read',
                    'スタッフモード › 申請管理 › 回答の閲覧',
                    '申請(回答の閲覧)',
                    '回答の閲覧が可能'
                ),
                'staff.contacts' => new PermissionInfo(
                    'staff.contacts',
                    'スタッフモード › お問い合わせ管理 › 全機能',
                    'お問い合わせ(全機能)',
                    'お問い合わせ管理の全機能を利用可能。今後、受信したお問い合わせを管理する機能が実装された場合、' .
                        'その機能の利用も可能になります。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.contacts.categories.read,edit,delete' => new PermissionInfo(
                    'staff.contacts.categories.read,edit,delete',
                    'スタッフモード › お問い合わせ管理 › お問い合わせ受付設定の閲覧と編集、削除',
                    'お問い合わせ(受付設定の削除)',
                    'お問い合わせ受付設定のお問い合わせ項目の閲覧と編集、作成、削除が可能'
                ),
                'staff.contacts.categories.read,edit' => new PermissionInfo(
                    'staff.contacts.categories.read,edit',
                    'スタッフモード › ユーザー情報管理 › お問い合わせ受付設定の閲覧と編集',
                    'お問い合わせ(受付設定の編集)',
                    'お問い合わせ受付設定のお問い合わせ項目の閲覧と編集、作成が可能'
                ),
                'staff.contacts.categories.read' => new PermissionInfo(
                    'staff.contacts.categories.read',
                    'スタッフモード › ユーザー情報管理 › お問い合わせ受付設定の閲覧',
                    'お問い合わせ(受付設定の閲覧)',
                    'お問い合わせ受付設定のお問い合わせ項目の閲覧が可能'
                ),
                'staff.permissions' => new PermissionInfo(
                    'staff.permissions',
                    'スタッフモード › スタッフの権限設定 › 全機能',
                    '権限設定(全機能)',
                    'スタッフの権限設定の全機能を利用可能。セキュリティのため、この権限を割り当てるユーザーは最小限にしてください。'
                ),
                'staff.permissions.read,edit' => new PermissionInfo(
                    'staff.permissions.read,edit',
                    'スタッフモード › スタッフの権限設定 › 閲覧と編集',
                    '権限設定(編集)',
                    'スタッフの権限設定の閲覧と編集が可能'
                ),
                'staff.permissions.read' => new PermissionInfo(
                    'staff.permissions.read',
                    'スタッフモード › スタッフの権限設定 › 閲覧',
                    '権限設定(閲覧)',
                    'スタッフの権限設定の閲覧が可能'
                ),
            ];
        }

        return $defined_permissions;
    }
}
