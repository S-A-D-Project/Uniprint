<div class="p-6">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-muted-foreground mb-1" for="name">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-input rounded-lg bg-background">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-foreground mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 border border-input rounded-lg bg-background">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-muted-foreground mb-1" for="username">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required
                       class="w-full px-3 py-2 border border-input rounded-lg bg-background">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-foreground mb-1" for="role_type">Role</label>
                <select id="role_type" name="role_type" class="w-full px-3 py-2 border border-input rounded-lg bg-background" required>
                    @php $rt = old('role_type', 'customer'); @endphp
                    <option value="customer" @if($rt==='customer') selected @endif>Customer</option>
                    <option value="business_user" @if($rt==='business_user') selected @endif>Business User</option>
                    <option value="admin" @if($rt==='admin') selected @endif>Admin</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-muted-foreground mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" required minlength="8"
                       class="w-full px-3 py-2 border border-input rounded-lg bg-background">
            </div>
            <div>
                <label class="block text-sm font-medium text-muted-foreground mb-1" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                       class="w-full px-3 py-2 border border-input rounded-lg bg-background">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
            <button type="button" class="px-6 py-2 border border-input rounded-lg hover:bg-secondary transition-smooth" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:shadow-glow transition-smooth">
                Create User
            </button>
        </div>
    </form>
</div>
