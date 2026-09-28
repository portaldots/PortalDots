<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Navigation;

use App\Services\Navigation\MenuItem;
use App\Services\Navigation\MenuRegistry;
use PHPUnit\Framework\TestCase;

class MenuRegistryTest extends TestCase
{
    public function test_clear_removes_items_hidden_at_the_time_of_replacement(): void
    {
        $visible = false;
        $registry = new MenuRegistry();
        $registry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'original',
            label: 'Original',
            route: 'staff.index',
            routeParams: [],
            activePattern: 'staff/*',
            icon: '',
            visible: function () use (&$visible): bool {
                return $visible;
            },
        ));

        $this->assertSame([], $registry->get(MenuRegistry::SECTION_STAFF));
        $registry->clear(MenuRegistry::SECTION_STAFF);
        $visible = true;
        $this->assertSame([], $registry->get(MenuRegistry::SECTION_STAFF));
    }
}
