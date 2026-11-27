<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Code Auditor</title>
    <style>
        body{font:16px system-ui,sans-serif;max-width:1200px;margin:3rem auto;padding:0 1rem;color:#17212b}h1{margin-bottom:.25rem}table{border-collapse:collapse;width:100%;margin-top:2rem}th,td{padding:.7rem;text-align:left;border-bottom:1px solid #ddd}th{background:#f2f5f7}tr:hover{background:#fafafa}code{font-size:.9em} .badge{color:#8a5300;background:#fff1cc;padding:.2rem .5rem;border-radius:4px}pre{white-space:pre-wrap;overflow-wrap:anywhere;background:#f2f5f7;padding:1rem}article{border:1px solid #ddd;border-radius:6px;padding:1rem;margin:1rem 0}dl{display:grid;grid-template-columns:12rem 1fr;gap:.5rem}dt{font-weight:bold}dd{margin:0}
    </style>
</head>
<body>
    <h1>Laravel Code Auditor</h1>
    <p><strong>{{ count($findings) }}</strong> potentially unused methods found.</p>
    <table>
        <thead><tr><th>File Path</th><th>Method Name</th><th>Line Number</th><th>Status</th><th>Git Author</th><th>Commit Hash</th><th>Commit Date</th></tr></thead>
        <tbody>
        @forelse ($findings as $finding)
            <tr><td><code>{{ $finding['file'] }}</code></td><td><code>{{ $finding['method'] }}</code></td><td>{{ $finding['line'] }}</td><td><span class="badge">{{ $finding['status'] }}</span></td><td>{{ $finding['author'] }}</td><td><code>{{ $finding['hash'] }}</code></td><td>{{ $finding['date'] }}</td></tr>
        @empty
            <tr><td colspan="7">No potentially unused methods found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <h2>Dead Code Origin Analysis</h2>
    <p>{{ count($investigations) }} findings have evidence that their last detected reference disappeared between HEAD and its first parent. Other findings: Origin not established.</p>
    @foreach ($investigations as $investigation)
        <article>
            <h3>{{ $investigation['method'] }} <small>({{ $investigation['file'] }}:{{ $investigation['line'] }})</small></h3>
            <dl>
                <dt>Last Modified By</dt><dd>{{ $investigation['author'] }}</dd>
                <dt>Potential Origin Author</dt><dd>{{ $investigation['origin_author'] }}</dd>
                <dt>Potential Origin Commit</dt><dd><code>{{ $investigation['origin_commit'] }}</code></dd>
                <dt>Commit Date</dt><dd>{{ $investigation['origin_date'] }}</dd>
                <dt>Commit Message</dt><dd>{{ $investigation['origin_message'] }}</dd>
                <dt>Previous Reference Count</dt><dd>{{ $investigation['previous_references'] }}</dd>
                <dt>Current Reference Count</dt><dd>{{ $investigation['current_references'] }}</dd>
                <dt>Detected Cause</dt><dd>{{ $investigation['cause'] }}</dd>
            </dl>
            <h4>Relevant Git Diff</h4>
            <pre>{{ $investigation['diff'] }}</pre>
        </article>
    @endforeach
</body>
</html>
