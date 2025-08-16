<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Todo;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123')
        ]);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => bcrypt('password123')
        ]);

        // Create todos for user1
        $todo1 = Todo::create([
            'title' => 'Complete project documentation',
            'description' => 'Write comprehensive documentation for the new feature',
            'completed' => false,
            'user_id' => $user1->id
        ]);

        $todo2 = Todo::create([
            'title' => 'Review code changes',
            'description' => 'Review pull requests and provide feedback',
            'completed' => true,
            'user_id' => $user1->id
        ]);

        // Create todos for user2
        $todo3 = Todo::create([
            'title' => 'Design new UI components',
            'description' => 'Create wireframes and mockups for the dashboard',
            'completed' => false,
            'user_id' => $user2->id
        ]);

        $todo4 = Todo::create([
            'title' => 'Update user manual',
            'description' => 'Revise the user manual with new features',
            'completed' => false,
            'user_id' => $user2->id
        ]);

        // Create todos for user3
        $todo5 = Todo::create([
            'title' => 'Fix bug in login system',
            'description' => 'Investigate and fix authentication issues',
            'completed' => false,
            'user_id' => $user3->id
        ]);

        // Create general relationships with metadata
        // User1 - Todo1: High priority work task
        $user1->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo1->id,
            relationshipType: 'todo_metadata',
            relationshipKey: 'priority_high',
            metadata: [
                'priority' => 'high',
                'due_date' => '2024-01-15',
                'category' => 'work',
                'estimated_hours' => 8
            ],
            sortOrder: 1
        );

        // User1 - Todo2: Medium priority review task
        $user1->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo2->id,
            relationshipType: 'todo_metadata',
            relationshipKey: 'priority_medium',
            metadata: [
                'priority' => 'medium',
                'due_date' => '2024-01-10',
                'category' => 'review',
                'estimated_hours' => 2
            ],
            sortOrder: 2
        );

        // User1 - Todo1: Favorite
        $user1->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo1->id,
            relationshipType: 'favorite',
            metadata: ['favorited_at' => now()->toISOString()]
        );

        // User2 - Todo3: High priority design task
        $user2->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo3->id,
            relationshipType: 'todo_metadata',
            relationshipKey: 'priority_high',
            metadata: [
                'priority' => 'high',
                'due_date' => '2024-01-20',
                'category' => 'design',
                'estimated_hours' => 12
            ],
            sortOrder: 1
        );

        // User2 - Todo4: Low priority documentation task
        $user2->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo4->id,
            relationshipType: 'todo_metadata',
            relationshipKey: 'priority_low',
            metadata: [
                'priority' => 'low',
                'due_date' => '2024-01-25',
                'category' => 'documentation',
                'estimated_hours' => 4
            ],
            sortOrder: 2
        );

        // User3 - Todo5: Urgent bug fix
        $user3->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo5->id,
            relationshipType: 'todo_metadata',
            relationshipKey: 'priority_urgent',
            metadata: [
                'priority' => 'high',
                'due_date' => '2024-01-08',
                'category' => 'bugfix',
                'estimated_hours' => 6
            ],
            sortOrder: 1
        );

        // Create some shared relationships
        $user1->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo1->id,
            relationshipType: 'shared',
            relationshipKey: 'team_collaboration',
            metadata: [
                'shared_with' => ['jane@example.com', 'bob@example.com'],
                'permissions' => ['read', 'comment'],
                'shared_at' => now()->toISOString()
            ]
        );

        $user2->createRelationship(
            relatedType: Todo::class,
            relatedId: $todo3->id,
            relationshipType: 'shared',
            relationshipKey: 'stakeholder_review',
            metadata: [
                'shared_with' => ['john@example.com'],
                'permissions' => ['read'],
                'shared_at' => now()->toISOString()
            ]
        );

        echo "Demo data seeded successfully!\n";
        echo "Created {$user1->name}, {$user2->name}, and {$user3->name}\n";
        echo "Created 5 todos with various relationships and metadata\n";
    }
}
