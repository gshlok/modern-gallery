<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-4">Type DELETE and your current password to permanently remove your account, including all your albums and images.</p>
                    <form method="POST" action="{{ route('user.destroy') }}" onsubmit="return confirm('This action cannot be undone. Proceed?');" class="space-y-3">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label for="confirm" class="block text-sm font-medium">Type DELETE to confirm</label>
                            <input id="confirm" name="confirm" type="text" class="border rounded p-2 w-full" required>
                            @error('confirm')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium">Current Password</label>
                            <input id="password" name="password" type="password" class="border rounded p-2 w-full" required>
                            @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded border border-red-700">Delete Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
