<?php

declare(strict_types=1);

namespace App\Services\Circles;

use DB;
use App\Eloquents\User;
use App\Eloquents\Circle;

class CirclesService
{
    /**
     * 企画を作成する
     *
     * @param User $leader 企画責任者
     * @param string $name 企画の名前
     * @param string $name_yomi 企画の名前(よみ)
     * @param string $group_name 企画団体の名前
     * @param string $group_name_yomi 企画団体の名前(よみ)
     * @return Circle
     */
    public function create(User $leader, string $name, string $name_yomi, string $group_name, string $group_name_yomi)
    {
        return DB::transaction(function () use ($leader, $name, $name_yomi, $group_name, $group_name_yomi) {
            $circle = Circle::create([
                'name' => $name,
                'name_yomi' => $name_yomi,
                'group_name' => $group_name,
                'group_name_yomi' => $group_name_yomi,
                'invitation_token' => $this->generateInvitationToken(),
            ]);

            $circle->users()->save($leader, ['is_leader' => true]);

            return $circle;
        });
    }

    public function update(Circle $circle, string $name, string $name_yomi, string $group_name, string $group_name_yomi)
    {
        return $circle->update([
            'name' => $name,
            'name_yomi' => $name_yomi,
            'group_name' => $group_name,
            'group_name_yomi' => $group_name_yomi,
        ]);
    }

    public function regenerateInvitationToken(Circle $circle)
    {
        return $circle->update([
            'invitation_token' => $this->generateInvitationToken(),
        ]);
    }

    public function addMember(Circle $circle, User $user)
    {
        return $circle->users()->save($user, ['is_leader' => false]);
    }

    public function removeMember(Circle $circle, User $user)
    {
        return $circle->users()->detach($user->id);
    }

    public function remove(Circle $circle)
    {
        return DB::transaction(function () use ($circle) {
            $circle->users()->detach();
            return $circle->delete();
        });
    }

    /**
     * 参加登録を提出する
     */
    public function submit(Circle $circle)
    {
        $circle->submitted_at = now();
        return $circle->save();
    }

    private function generateInvitationToken()
    {
        return bin2hex(random_bytes(16));
    }
}
