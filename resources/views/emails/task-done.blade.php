<h2>New Task Created</h2>
<p>Hello {{ $task->user->name }},</p>
<p>Your task "<strong>{{ $task->title }}</strong>" has been marked as <strong>done</strong>.</p>
<p>Status: {{ ucfirst($task->status->getLabel()) }}</p>
<p>Completed at: {{ reformatDate($task->updated_at) }}</p>
