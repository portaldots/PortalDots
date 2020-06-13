@extends('v2.layouts.app')

@section('content')
<app-container>
    <list-view>
        <list-view-card>
            <div class="py-spacing">
                <app-dropdown v-bind:items="{{ $items }}" name="hoge">
                    <template v-slot:button="{ toggle, props }">
                        <button class="btn is-primary" v-on:click="toggle" v-bind="props">開く</button>
                    </template>
                    <template v-slot:item="{ item }">
                        <app-dropdown-item component-is="a" :href="item.href">
                            @{{ item.label }}
                        </app-dropdown-item>
                    </template>
                </app-dropdown>
                <app-dropdown v-bind:items="{{ $items }}" name="hoge">
                    <template v-slot:button="{ toggle, props }">
                        <button class="btn is-primary" v-on:click="toggle" v-bind="props">開く</button>
                    </template>
                    <template v-slot:item="{ item }">
                        <app-dropdown-item component-is="a" :href="item.href">
                            @{{ item.label }}
                        </app-dropdown-item>
                    </template>
                </app-dropdown>
            </div>
            <p>つぎのぎょうだおｙ−</p>
        </list-view-card>
    </list-view>
</app-container>
@endsection
