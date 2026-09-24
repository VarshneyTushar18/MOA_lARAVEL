@props([
    'showRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'extra' => null,
])

<div class="d-flex gap-1 flex-wrap">
    @if($showRoute)
        <a href="{{ $showRoute }}" class="btn btn-sm btn-outline-secondary" title="View">
            <i class="fa-solid fa-eye"></i>
        </a>
    @endif

    @if($editRoute)
        <a href="{{ $editRoute }}" class="btn btn-sm btn-primary" title="Edit">
            <i class="fa-solid fa-pen"></i>
        </a>
    @endif

    {!! $extra ?? '' !!}

    @if($deleteRoute)
        <form action="{{ $deleteRoute }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record permanently?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                <i class="fa-solid fa-trash"></i>
            </button>
        </form>
    @endif
</div>
