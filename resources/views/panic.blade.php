<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSTEM PANIC - EMERGENCY ACCESS</title>
    <style>
        body { background: #000; color: #0f0; font-family: 'Courier New', monospace; padding: 20px; line-height: 1.5; }
        .container { max-width: 1000px; margin: 0 auto; border: 1px solid #0f0; padding: 20px; box-shadow: 0 0 20px #0f0; }
        h1 { border-bottom: 2px solid #0f0; padding-bottom: 10px; color: #f00; text-blink: 1s; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { border: 1px solid #040; padding: 15px; }
        .logs { background: #111; padding: 10px; height: 300px; overflow-y: scroll; font-size: 12px; border: 1px solid #060; }
        button { background: #f00; color: #fff; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; }
        button:hover { background: #c00; }
        .status-item { margin-bottom: 10px; }
        .status-val { font-weight: bold; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ SYSTEM PANIC OVERRIDE</h1>
        
        <div class="grid">
            <div class="card">
                <h3>System Status</h3>
                <div class="status-item">PHP Version: <span class="status-val">{{ $system_status['php_version'] }}</span></div>
                <div class="status-item">Disk Free: <span class="status-val">{{ $system_status['disk_free'] }}</span></div>
                <div class="status-item">Database: <span class="status-val">{{ $system_status['db_connection'] }}</span></div>
                
                <hr style="border: 0.5px solid #040">
                
                <h3>Emergency Actions</h3>
                <form action="{{ url('/panic/'.$key.'/reset') }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('INITIATE CACHE PURGE?')">FORCE CACHE CLEAR</button>
                </form>
                @if(session('message'))
                    <p style="color: yellow">{{ session('message') }}</p>
                @endif
            </div>
            
            <div class="card">
                <h3>Recent Terminal Output</h3>
                <div class="logs">
                    @foreach($logs as $log)
                        <div>{{ $log }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <p style="margin-top: 20px; color: #444; font-size: 10px;">AUTHORIZED PERSONNEL ONLY. ALL ACTIONS ARE LOGGED.</p>
    </div>
</body>
</html>
