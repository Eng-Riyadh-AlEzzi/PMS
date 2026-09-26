<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_on_initial_assignment()
    {
        Notification::fake();

        $manager = User::factory()->create(['role' => 'manager']);
        $employee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => null]);

        $this->actingAs($manager)->putJson("/api/tasks/{$task->id}/assign", [
            'assigned_to' => $employee->id,
        ]);

        Notification::assertSentTo($employee, TaskAssignedNotification::class);
    }

    public function test_notification_is_sent_on_reassignment()
    {
        Notification::fake();

        $manager = User::factory()->create(['role' => 'manager']);
        $employeeA = User::factory()->create();
        $employeeB = User::factory()->create();
        
        $task = Task::factory()->create(['assigned_to' => $employeeA->id]);

        $this->actingAs($manager)->putJson("/api/tasks/{$task->id}/assign", [
            'assigned_to' => $employeeB->id,
        ]);

        Notification::assertSentTo($employeeB, TaskAssignedNotification::class);
        Notification::assertNotSentTo($employeeA, TaskAssignedNotification::class);
    }
}
