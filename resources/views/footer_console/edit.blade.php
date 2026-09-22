@extends('layout.console')

@section('content')
<section class="w3-padding">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0">Manage Footer</h2>
        <a href="/console/dashboard" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <p class="text-muted small mb-4">Edit everything shown in the website footer: logos, link columns, contact details, copyright, and legal links.</p>

    <form method="post" action="/console/footer" enctype="multipart/form-data" novalidate class="console-form card">
        @csrf

        <h4 class="mb-3">Branding</h4>
        <div class="w3-margin-bottom">
            <label for="logo">Main Footer Logo</label>
            @if($existing_logo)
                <div class="console-preview mb-2">
                    <img src="{{ asset('storage/'.$existing_logo) }}" alt="">
                </div>
            @endif
            <input type="file" class="form-control" name="logo" id="logo" accept="image/*">
        </div>
        <div class="w3-margin-bottom">
            <label for="logo_alt">Logo Alt Text</label>
            <input type="text" class="form-control" name="logo_alt" id="logo_alt" value="{{ $logo_alt }}">
        </div>

        <h4 class="mb-2 mt-4">Sponsor Logos</h4>
        <p class="w3-small text-muted">Optional small logos shown below the main footer logo. Leave empty to use the default site logos on the frontend.</p>
        <div id="sponsors_wrapper">
            @foreach($sponsors as $idx => $sponsor)
                <div class="w3-border w3-padding w3-margin-bottom repeater-item" data-index="{{ $idx }}">
                    <input type="hidden" name="sponsors[{{ $idx }}][id]" value="{{ $sponsor['id'] ?? '' }}">
                    <div class="w3-margin-bottom">
                        <label>Alt Text</label>
                        <input type="text" class="form-control" name="sponsors[{{ $idx }}][alt]" value="{{ $sponsor['alt'] ?? '' }}">
                    </div>
                    <div class="w3-margin-bottom">
                        <label>Logo Image</label>
                        @if(!empty($sponsor['existing_image']))
                            <div class="console-preview mb-2">
                                <img src="{{ asset('storage/'.$sponsor['existing_image']) }}" alt="">
                            </div>
                        @endif
                        <input type="file" class="form-control" name="sponsors[{{ $idx }}][image]" accept="image/*">
                    </div>
                    <div class="w3-margin-bottom">
                        <label>Sort Order</label>
                        <input type="number" class="form-control" name="sponsors[{{ $idx }}][sort_order]" value="{{ $sponsor['sort_order'] ?? 0 }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="add_sponsor" class="btn btn-primary btn-sm mb-4">Add Sponsor Logo</button>

        <h4 class="mb-3">Useful Links Column</h4>
        <div class="w3-margin-bottom">
            <label for="useful_links_title">Column Title</label>
            <input type="text" class="form-control" name="useful_links_title" id="useful_links_title" value="{{ $useful_links_title }}">
        </div>
        <div id="useful_links_wrapper">
            @foreach($useful_links as $idx => $link)
                @include('footer_console.partials.link-row', ['prefix' => 'useful_links', 'idx' => $idx, 'link' => $link])
            @endforeach
        </div>
        <button type="button" id="add_useful_link" class="btn btn-primary btn-sm mb-4" data-prefix="useful_links" data-wrapper="useful_links_wrapper">Add Link</button>

        <h4 class="mb-3">Important Links Column</h4>
        <div class="w3-margin-bottom">
            <label for="important_links_title">Column Title</label>
            <input type="text" class="form-control" name="important_links_title" id="important_links_title" value="{{ $important_links_title }}">
        </div>
        <div id="important_links_wrapper">
            @foreach($important_links as $idx => $link)
                @include('footer_console.partials.link-row', ['prefix' => 'important_links', 'idx' => $idx, 'link' => $link])
            @endforeach
        </div>
        <button type="button" id="add_important_link" class="btn btn-primary btn-sm mb-4" data-prefix="important_links" data-wrapper="important_links_wrapper">Add Link</button>

        <h4 class="mb-3">Contact Information</h4>
        <div class="w3-margin-bottom">
            <label for="contact_title">Section Title</label>
            <input type="text" class="form-control" name="contact_title" id="contact_title" value="{{ $contact_title }}">
        </div>
        <div class="w3-margin-bottom">
            <label for="address">Address</label>
            <textarea class="form-control" name="address" id="address" rows="4">{{ $address }}</textarea>
        </div>
        <div class="w3-margin-bottom">
            <label for="phone">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone" value="{{ $phone }}">
        </div>
        <div class="w3-margin-bottom">
            <label for="email">Email</label>
            <input type="text" class="form-control" name="email" id="email" value="{{ $email }}">
        </div>

        <h4 class="mb-3 mt-4">Footer Bottom Bar</h4>
        <div class="w3-margin-bottom">
            <label for="copyright">Copyright Text</label>
            <input type="text" class="form-control" name="copyright" id="copyright" value="{{ $copyright }}">
        </div>
        <div id="legal_links_wrapper">
            @foreach($legal_links as $idx => $link)
                @include('footer_console.partials.link-row', ['prefix' => 'legal_links', 'idx' => $idx, 'link' => $link])
            @endforeach
        </div>
        <button type="button" id="add_legal_link" class="btn btn-primary btn-sm mb-4" data-prefix="legal_links" data-wrapper="legal_links_wrapper">Add Legal Link</button>

        <button type="submit" class="btn btn-success">Save Footer</button>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var counters = {
        sponsors: document.querySelectorAll('#sponsors_wrapper .repeater-item').length,
        useful_links: document.querySelectorAll('#useful_links_wrapper .repeater-item').length,
        important_links: document.querySelectorAll('#important_links_wrapper .repeater-item').length,
        legal_links: document.querySelectorAll('#legal_links_wrapper .repeater-item').length
    };

    function sponsorTemplate(index) {
        return '' +
            '<div class="w3-border w3-padding w3-margin-bottom repeater-item" data-index="' + index + '">' +
                '<input type="hidden" name="sponsors[' + index + '][id]" value="">' +
                '<div class="w3-margin-bottom"><label>Alt Text</label><input type="text" class="form-control" name="sponsors[' + index + '][alt]"></div>' +
                '<div class="w3-margin-bottom"><label>Logo Image</label><input type="file" class="form-control" name="sponsors[' + index + '][image]" accept="image/*"></div>' +
                '<div class="w3-margin-bottom"><label>Sort Order</label><input type="number" class="form-control" name="sponsors[' + index + '][sort_order]" value="0"></div>' +
                '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
            '</div>';
    }

    function linkTemplate(prefix, index) {
        return '' +
            '<div class="w3-border w3-padding w3-margin-bottom repeater-item" data-index="' + index + '">' +
                '<input type="hidden" name="' + prefix + '[' + index + '][id]" value="">' +
                '<div class="w3-margin-bottom"><label>Link Label</label><input type="text" class="form-control" name="' + prefix + '[' + index + '][title]"></div>' +
                '<div class="w3-margin-bottom"><label>URL</label><input type="text" class="form-control" name="' + prefix + '[' + index + '][url]" placeholder="/about or https://..."></div>' +
                '<div class="w3-margin-bottom"><label>Sort Order</label><input type="number" class="form-control" name="' + prefix + '[' + index + '][sort_order]" value="0"></div>' +
                '<button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>' +
            '</div>';
    }

    document.getElementById('add_sponsor').addEventListener('click', function () {
        var wrapper = document.getElementById('sponsors_wrapper');
        wrapper.insertAdjacentHTML('beforeend', sponsorTemplate(counters.sponsors));
        counters.sponsors += 1;
    });

    document.querySelectorAll('[data-prefix]').forEach(function (button) {
        button.addEventListener('click', function () {
            var prefix = button.getAttribute('data-prefix');
            var wrapper = document.getElementById(button.getAttribute('data-wrapper'));
            wrapper.insertAdjacentHTML('beforeend', linkTemplate(prefix, counters[prefix]));
            counters[prefix] += 1;
        });
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-item')) {
            var block = event.target.closest('.repeater-item');
            if (block) block.remove();
        }
    });
});
</script>
@endsection
