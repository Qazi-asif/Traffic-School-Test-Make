# Add Customer Segments to Admin Navigation

## Quick Navigation Setup

Add this link to your admin navigation menu to access the Customer Segmentation system.

### Option 1: Add to Navbar Component

**File: `resources/views/components/navbar.blade.php`**

Find the admin menu section and add:

```blade
<!-- Customer Segments -->
@if(auth()->check() && (auth()->user()->role === 'super-admin' || auth()->user()->role === 'admin'))
<li>
    <a href="{{ route('admin.customers.segments') }}" 
       class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 {{ request()->is('admin/customers/*') ? 'bg-gray-100 font-semibold' : '' }}">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        Customer Segments
    </a>
</li>
@endif
```

### Option 2: Add to Admin Dashboard

**File: `resources/views/dashboard.blade.php`**

Add a card in the admin dashboard:

```blade
<!-- Customer Segments Card -->
<div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Customer Segments</h3>
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
    </div>
    <p class="text-gray-600 mb-4">Analyze and manage customer enrollment segments</p>
    <a href="{{ route('admin.customers.segments') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        View Segments
    </a>
</div>
```

### Option 3: Direct Link

Simply navigate to:
```
http://yoursite.com/admin/customers/segments
```

Or bookmark it in your browser!

### Quick Access URLs

- **Dashboard**: `/admin/customers/segments`
- **Completed Monthly**: `/admin/customers/completed-monthly`
- **Paid Incomplete**: `/admin/customers/paid-incomplete`
- **Abandoned**: `/admin/customers/abandoned`
- **Expiring Soon**: `/admin/customers/expiring-soon`

---

## Testing Navigation

1. Log in as admin or super-admin
2. Navigate to `/admin/customers/segments`
3. You should see the segment dashboard with 8 cards
4. Click any card to view that segment

---

## Troubleshooting

**404 Error?**
- Clear route cache: `php artisan route:clear`
- Check middleware: Ensure you're logged in as admin

**No Data?**
- Check if you have enrollments in database
- Verify `user_course_enrollments` table has data

**Permission Denied?**
- Verify your user role is 'admin' or 'super-admin'
- Check middleware in routes/web.php
