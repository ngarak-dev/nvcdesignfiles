<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class UserManagemet extends Component
{

    public $deletingUser = false;
    public string $name = '';
    public string $email = '';

    public function registerUser () {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        ]);

        $validated['password'] = Hash::make('password');
        event(new Registered(($user = User::create($validated))));

        $this->modal('add-user')->close();
        session()->flash('message', 'User added successfully !');
    }
    public function render()
    {
        return view('livewire.user-managemet', [
            'users' => User::paginate(10)
        ]);
    }
}
