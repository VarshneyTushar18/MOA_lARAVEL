<div class="w3-border w3-padding w3-margin-bottom repeater-item" data-index="{{ $idx }}">
    <input type="hidden" name="{{ $prefix }}[{{ $idx }}][id]" value="{{ $link['id'] ?? '' }}">
    <div class="w3-margin-bottom">
        <label>Link Label</label>
        <input type="text" class="form-control" name="{{ $prefix }}[{{ $idx }}][title]" value="{{ $link['title'] ?? '' }}">
    </div>
    <div class="w3-margin-bottom">
        <label>URL</label>
        <input type="text" class="form-control" name="{{ $prefix }}[{{ $idx }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="/about or https://...">
    </div>
    <div class="w3-margin-bottom">
        <label>Sort Order</label>
        <input type="number" class="form-control" name="{{ $prefix }}[{{ $idx }}][sort_order]" value="{{ $link['sort_order'] ?? 0 }}">
    </div>
    <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
</div>
