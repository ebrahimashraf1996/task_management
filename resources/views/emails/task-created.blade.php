<h2>New Task Created</h2>
<p>Hello {{ $task->user->name }},</p>
<p>A new task "<strong>{{ $task->title }}</strong>" has been created for you.</p>
<p>Status: {{ ucfirst($task->status->getLabel()) }}</p>
<p>Created at: {{ reformatDate($task->created_at) }}</p>
