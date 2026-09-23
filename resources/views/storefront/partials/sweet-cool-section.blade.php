@php
    $sweetCoolPage = \App\Models\SweetCoolPage::singleton();
    $sweetCoolLogo = $sweetCoolPage->logo_path
        ? \Illuminate\Support\Facades\Storage::url($sweetCoolPage->logo_path)
        : asset('storefront-backgrounds/sweetcool.png');
    $teaserTitle = trim((string) $sweetCoolPage->teaser_title);
    $teaserDescription = trim((string) $sweetCoolPage->teaser_description);
@endphp

<section
    class="sweet-cool-section sweet-cool-teaser-section"
    id="sweet-cool"
>
    <div class="store-container">
        <div class="sweet-cool-teaser-heading" aria-hidden="true">
            <span>FACTORY</span>
        </div>

        <div class="sweet-cool-teaser-wrap">
            <a
                href="{{ route('sweet-cool.show', ['brand' => $brand->slug]) }}"
                class="sweet-cool-teaser-card"
            >
                <div class="sweet-cool-teaser-stage">
                    <img
                        src="{{ $sweetCoolLogo }}"
                        alt="Sweet Cool"
                        class="sweet-cool-teaser-logo"
                    >
                </div>

                @if ($teaserTitle !== '' || $teaserDescription !== '')
                    <div class="sweet-cool-teaser-copy">
                        @if ($teaserTitle !== '')
                            <h2>{{ $teaserTitle }}</h2>
                        @endif

                        @if ($teaserDescription !== '')
                            <p>{{ $teaserDescription }}</p>
                        @endif
                    </div>
                @endif
            </a>

            <a
                href="{{ route('sweet-cool.show', ['brand' => $brand->slug]) }}"
                class="sweet-cool-teaser-button"
            >
                {{ $sweetCoolPage->teaser_button_text ?: "Let's Dive into SweetCool" }}
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
