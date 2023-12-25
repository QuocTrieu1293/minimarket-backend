<div>
    <h1>Setting</h1>
    <button onclick="window.history.back()">Back</button>
    <p>User: {{ Auth::user() }}</p>
    <x-filament::avatar
    src="https://filamentphp.com/dan.jpg"
    alt="Dan Harrin"/>
    {{dd(session())}}
</div>
