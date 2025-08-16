<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carlxaeron\General\Traits\HasGeneralMaps;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasGeneralMaps;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the todos for the user through general maps.
     * This method returns a collection, not a relationship instance.
     */
    public function getTodos()
    {
        return $this->getRelatedModels(\App\Models\Todo::class, 'todo_owner');
    }

    /**
     * Associate a todo with this user.
     */
    public function addTodo(\App\Models\Todo $todo, ?array $metadata = null)
    {
        return $this->addRelatedModel($todo, 'todo_owner', null, $metadata ?? []);
    }

    /**
     * Remove a todo association from this user.
     */
    public function removeTodo(\App\Models\Todo $todo)
    {
        return $this->removeRelatedModel($todo, 'todo_owner');
    }
}
