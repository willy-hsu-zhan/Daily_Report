<nav class="navbar navbar-expand-lg bg-primary navbar-primary">
    <a href="/home" class="navbar-brand ms-4">
        <img class="mr-2" src="{{ asset('images/LOGO.png') }}" alt="" width="30" height="30">
        example demo
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarText">
            <dl class="navbar-nav ms-auto mb-2 mb-lg-0 me-2">
                <div class="category">
                    @foreach ($data as $categoryName => $categoryLink)
                        <li class="{{ $currentIndex === $loop->index ? 'active' : '' }}" index="{{ $loop->index }}"
                            wire:click="setCurrentIndex({{ $loop->index }}) ">
                            <a {{ $categoryLink }} wire:click="updateCategoryLink('{{ $categoryLink }}')"
                                class="{{ $currentIndex === $loop->index ? 'category-link' : 'nav-link' }}">
                                {{ $categoryName }}
                            </a>
                        </li>
                    @endforeach
                </div>
            </dl>
        </div>
        <div class="right collapse navbar-collapse">
            <ul class="navbar-nav" style="position: absolute; top: 16px; right: 31px;">
                <li class="nav-item user">
                    使用者名稱：{{ Auth::user()?->name }}
                </li>
            </ul>
        </div>
    </div>
</nav>
