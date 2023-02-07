<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <h3 class="font-bold text-2xl">{{__("Announcements")}}</h3>
    <div class="flex justify-end">
        <a href="/announcements/create" class="inline-flex items-center px-4 py-2 bg-spred border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-spred-60 focus:bg-spred-80 active:bg-spred-120 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Neues Announcement</a>
    </div>

    <div class="ticker-admin-announcements mt-8">
        @forelse($announcements as $announcement)
            <div class="ticker-admin-announcement-item p-4 bg-gray-100 rounded-sm mb-5">
                <h4 class="font-bold text-xl mb-2 text-spred">{{$announcement->title}}</h4>
                <p class="text-gray-500 mb-2 mt-0">{{$announcement->created_at->diffForHumans()}}@if($announcement->created_at != $announcement->updated_at) | Updated : {{$announcement->updated_at->diffForHumans()}}@endif</p>
                <p class="text-gray-500 mb-4 mt-0">{{$announcement->user->name}}</p>
                <div class="max-w-xl max-h-36 overflow-hidden ticker-admin-announcement-excerpt">{!! $announcement->getHtmlContent() !!}</div>
                <div class="ticker-admin-announcement-actions mt-4 flex gap-4">
                    <a href="{{route('announcements.edit', $announcement)}}" class="text-blue-500">{{__("Edit")}}</a>
                    <form action="{{route('announcements.destroy', $announcement)}}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-spred">{{__("Delete")}}</button>
                    </form>
                </div>
            </div>
        @empty
        <p class="text-gray-500">{{__("Keine Announcments")}}</p>
        @endforelse
        @if ($announcements->hasPages())
            <div class="pagination-wrapper">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
