<?php

namespace Tests\Feature;

use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckPermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->states('staff')->create();
    }

    /**
     * 権限識別名リスト
     */
    public function permissions()
    {
        return [
            'staff.users',
            'staff.users.read,export',
            'staff.users.read,edit',
            'staff.users.read',
            'staff.circles',
            'staff.circles.participation_types',
            'staff.circles.read,edit,delete',
            'staff.circles.read,edit',
            'staff.circles.read,send_email',
            'staff.circles.read,export',
            'staff.circles.read',
            'staff.tags',
            'staff.tags.read,edit,delete',
            'staff.tags.read,edit',
            'staff.tags.read,export',
            'staff.tags.read',
            'staff.places',
            'staff.places.read,edit,delete',
            'staff.places.read,edit',
            'staff.places.read,export',
            'staff.places.read',
            'staff.pages',
            'staff.pages.read,edit,send_emails',
            'staff.pages.read,edit,delete',
            'staff.pages.read,edit',
            'staff.pages.read,export',
            'staff.pages.read',
            'staff.documents',
            'staff.documents.read,edit,delete',
            'staff.documents.read,edit',
            'staff.documents.read,export',
            'staff.documents.read',
            'staff.forms',
            'staff.forms.read,edit,duplicate',
            'staff.forms.read,edit',
            'staff.forms.read,export',
            'staff.forms.read',
            'staff.forms.answers.read,edit',
            'staff.forms.answers.read,export',
            'staff.forms.answers.read',
            'staff.contacts',
            'staff.contacts.categories.read,edit,delete',
            'staff.contacts.categories.read,edit',
            'staff.contacts.categories.read',
            'staff.permissions',
            'staff.permissions.read,edit',
            'staff.permissions.read',
        ];
    }

    private function getRouteNamePrefixesByPermissions()
    {
        return array_reduce($this->permissions(), function ($carry, $identifier) {
            $exploded_identifier = explode('.', $identifier);
            // 権限識別名に対応するルーティング名
            $route_name_prefix = implode(
                '.',
                array_slice($exploded_identifier, 0, count($exploded_identifier) - 1)
            );
            if ($route_name_prefix === 'staff') {
                return $carry;
            } elseif (isset($carry[$route_name_prefix])) {
                $carry[$route_name_prefix][] = $identifier;
                return $carry;
            } else {
                $carry[$route_name_prefix] = [$identifier];
                return $carry;
            }
        }, []);
    }

    /**
     * @test
     */
    public function permissionsに全ての権限が含まれているか()
    {
        $defined_permissions = array_keys(Permission::getDefinedPermissions());
        $diff = array_diff($defined_permissions, $this->permissions());
        $this->assertCount(
            0,
            $diff,
            'PermissionモデルのgetDefinedPermissionsに新しい権限を作成した場合、 CheckPermissionsTest を修正する必要があります。'
        );
    }

    /**
     * @test
     */
    public function 権限があれば各機能のトップページにアクセスできる()
    {
        foreach ($this->getRouteNamePrefixesByPermissions() as $route_name_prefix => $permissions) {
            foreach ($permissions as $permission) {
                if (
                    $permission === 'staff.circles.participation_types'
                    || $route_name_prefix === 'staff.forms.answers'
                ) {
                    // これらについてのテストの実装は保留
                    continue;
                }

                Permission::create(['name' => $permission]);
                $this->user->syncPermissions([$permission]);
                $response = $this->actingAs($this->user)
                    ->withSession(['staff_authorized' => true])
                    ->get(route($route_name_prefix . '.index'));
                $this->assertTrue(
                    $response->isOk(),
                    "権限 " . $permission . " で " . $route_name_prefix . " にアクセスできません"
                );
            }
        }
    }

    /**
     * @test
     */
    public function 権限がなければ各機能のトップページにアクセスできない()
    {
        foreach ($this->getRouteNamePrefixesByPermissions() as $route_name_prefix => $permissions) {
            foreach ($permissions as $permission) {
                if ($permission === 'staff.circles.custom_form' || $route_name_prefix === 'staff.forms.answers') {
                    // これらについてのテストの実装は保留
                    continue;
                }

                $response = $this->actingAs($this->user)
                    ->withSession(['staff_authorized' => true])
                    ->get(route($route_name_prefix . '.index'));
                $this->assertEquals(
                    403,
                    $response->getStatusCode(),
                    "権限がない場合は " . $route_name_prefix .
                        " にアクセスできないようにする必要があります。権限 : " .
                        $permission
                );
            }
        }
    }
}
