<?php

namespace App\Exports;

use App\Eloquents\Group;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GroupsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Group::with(['users' => function (Builder $query) {
            $query->wherePivot('role', 'owner');
        }])
            ->normal()
            ->get();
    }

    /**
     * @param Group $group
     * @return array
     */
    public function map($group): array
    {
        $owners = $group->users->filter(function ($user) {
            return $user->pivot->role === "owner";
        });

        // Ownerは1団体につき1人のみ。
        $owner = count($owners) > 0 ? $owners[0] : null;

        return [
            $group->id,
            $group->name,
            $group->name_yomi,
            !empty($owner)
                ? "{$owner->name}(ID:{$owner->id},{$owner->student_id})"
                : '',
            $group->notes,
            $group->created_at,
            $group->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            '団体ID',
            '団体名',
            '団体名(よみ)',
            '責任者',
            'スタッフ用メモ',
            '作成日時',
            '更新日時',
        ];
    }
}
