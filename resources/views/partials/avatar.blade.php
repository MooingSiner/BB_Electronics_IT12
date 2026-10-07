@php($avatarUser = auth()->user())
<div class="{{ $avatarSize ?? 'w-7 h-7 text-xs' }} rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden font-bold {{ $avatarClass ?? '' }}"
     style="background-color:#E0CD66;color:#363E48">
    @if ($avatarUser->profilePhotoUrl())
        <img src="{{ $avatarUser->profilePhotoUrl() }}" alt="" class="w-full h-full object-cover">
    @else
        {{ $avatarUser->initials() }}
    @endif
</div>
