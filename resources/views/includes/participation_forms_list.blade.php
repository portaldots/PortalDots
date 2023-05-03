<layout-row grid-template-columns="repeat(auto-fill, minmax(240px, 1fr))">
    @foreach ($participation_types as $participation_type)
        <layout-column>
            <card-link href="{{ route('circles.create', ['participation_type' => $participation_type]) }}">
                <template v-slot:title>
                    {{ $participation_type->name }}
                </template>
                <template v-slot:description>
                    <p class="text-small text-primary">
                        @datetime($participation_type->form->close_at)まで受付
                    </p>
                    <p>{{ $participation_type->description }}</p>
                </template>
            </card-link>
        </layout-column>
    @endforeach
</layout-row>
