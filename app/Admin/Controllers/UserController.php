<?php

namespace App\Admin\Controllers;

use App\Eloquents\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ユーザー';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('id', 'Id');
        $grid->column('student_id', '学籍番号');
        $grid->column('name_family', '名前(姓)');
        $grid->column('name_family_yomi', '名前(姓・よみ)');
        $grid->column('name_given', 'Name given');
        $grid->column('name_given_yomi', 'Name given yomi');
        $grid->column('email', 'Email');
        $grid->column('tel', 'Tel');
        $grid->column('is_staff', 'Is staff')->bool();
        $grid->column('email_verified_at', 'Email verified at');
        $grid->column('univemail_verified_at', 'Univemail verified at');
        $grid->column('is_signed_up', 'Is signed up')->bool();
        $grid->column('password', 'Password');
        $grid->column('notes', 'Notes');
        $grid->column('remember_token', 'Remember token');
        $grid->column('created_at', 'Created at');
        $grid->column('updated_at', 'Updated at');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

        $show->field('id', 'Id');
        $show->field('student_id', '学籍番号');
        $show->field('name_family', '名前(姓)');
        $show->field('name_family_yomi', '名前(姓・よみ)');
        $show->field('name_given', 'Name given');
        $show->field('name_given_yomi', 'Name given yomi');
        $show->field('email', 'Email');
        $show->field('tel', 'Tel');
        $show->field('is_staff', 'Is staff')->bool();
        $show->field('email_verified_at', 'Email verified at');
        $show->field('univemail_verified_at', 'Univemail verified at');
        $show->field('is_signed_up', 'Is signed up')->bool();
        $show->field('password', 'Password');
        $show->field('notes', 'Notes');
        $show->field('remember_token', 'Remember token');
        $show->field('created_at', 'Created at');
        $show->field('updated_at', 'Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('student_id', '学籍番号');
        $form->text('name_family', '名前(姓)');
        $form->text('name_family_yomi', '名前(姓・よみ)');
        $form->text('name_given', 'Name given');
        $form->text('name_given_yomi', 'Name given yomi');
        $form->email('email', 'Email');
        $form->text('tel', 'Tel');
        $form->switch('is_staff', 'Is staff');
        $form->datetime('email_verified_at', 'Email verified at')->default(date('Y-m-d H:i:s'));
        $form->datetime('univemail_verified_at', 'Univemail verified at')->default(date('Y-m-d H:i:s'));
        $form->switch('is_signed_up', 'Is signed up');
        $form->password('password', 'Password');
        $form->textarea('notes', 'Notes');
        $form->text('remember_token', 'Remember token');

        return $form;
    }
}
