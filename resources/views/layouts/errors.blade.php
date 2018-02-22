@if ($errors->count() > 0)
    <article class="message is-danger">
        <div class="message-body">
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    </article>
@endif