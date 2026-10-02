<list-view-form-group label-for="{{ $inputId }}">
    <template v-slot:label>担当者</template>
    <form method="post" action="{{ route('staff.threads.assignee.update', ['thread' => $thread]) }}">
        @csrf
        @method('patch')
        <input type="hidden" name="lock_version" value="{{ $thread->lock_version }}">
        <select id="{{ $inputId }}" name="assignee_id" class="form-control">
            <option value="">未設定</option>
            @foreach ($staffUsers as $staffUser)
                <option value="{{ $staffUser->id }}"
                    {{ (int)$thread->assignee_id === $staffUser->id ? 'selected' : '' }}>
                    {{ $staffUser->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn is-secondary is-sm mt-spacing-sm">更新</button>
    </form>
</list-view-form-group>
