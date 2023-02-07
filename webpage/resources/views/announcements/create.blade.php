<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Neues Announcment') }}
        </h2>
    </x-slot>

    <form method="POST" action="{{ route('announcements.store') }}" class="flex flex-wrap ticker-admin-announcement-form">
        @csrf

        <!-- Title -->
        <div class="mt-4 w-1/2 pr-4">
            <x-input-label for="title" :value="__('Titel')" />

            <x-text-input id="title" class="block mt-1 w-full"
                            type="text"
                            name="title"
                            required />

            <x-input-error :messages="$errors->get('title')" class="mt-2" />
        </div>

        <div class="mt-4 w-1/2 pl-4">
            <x-input-label for="subtitle" :value="__('Untertitel')" />

            <x-text-input id="subtitle" class="block mt-1 w-full"
                            type="text"
                            name="subtitle"
                            required />

            <x-input-error :messages="$errors->get('subtitle')" class="mt-2" />
        </div>

        <div class="mt-4 w-full">
            <x-input-label for="editor" :value="__('Inhalt')" />
            <p name="editor" id="editor" class="bg-gray-100 rounded-sm p-2">
            </p>

            <x-input-error :messages="$errors->get('content')" class="mt-2" />
        </div>

        <input type="hidden" name="content" id="content" >
        <input type="hidden" name="type" value="news" >
        <input type="hidden" name="user_id" value={{auth()->user()->id}} >



        <div class="flex items-center justify-end mt-4 w-full">
            <x-primary-button class="ml-4 bg-spred">
                {{ __('Announcement posten') }}
            </x-primary-button>
        </div>
    </form>

</x-app-layout>
