@foreach ($logEntries as $logEntry)
    <li>
        <div class="d-flex">
            <img src="images/uploads/user-photos/{{ $logEntry['image'] }}" alt="user">
            <div>
                <p class="text-info mb-1">{{ $logEntry['user'] }}</p>
                <p class="mb-0">{{ $logEntry['action'] }}</p>
                <small>{{ $logEntry['time'] }}</small>
            </div>
        </div>
    </li>
@endforeach
