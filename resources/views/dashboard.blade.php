<x-app-layout>
<div class="flex h-screen bg-slate-50">
  @include('components.sidebar-component')

  <!-- Main Content -->
  <main class="flex-1 overflow-auto">
  

    <!-- Dashboard Content -->
    <div class="p-8">
      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-6 border border-slate-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Total Users</p>
              <p class="text-3xl font-bold text-slate-900 mt-2">2,847</p>
              <p class="text-sm text-emerald-600 mt-2">↑ 12.5% from last month</p>
            </div>
            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-slate-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Revenue</p>
              <p class="text-3xl font-bold text-slate-900 mt-2">$45,231</p>
              <p class="text-sm text-emerald-600 mt-2">↑ 8.2% from last month</p>
            </div>
            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-slate-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Active Sessions</p>
              <p class="text-3xl font-bold text-slate-900 mt-2">1,423</p>
              <p class="text-sm text-red-600 mt-2">↓ 3.1% from last month</p>
            </div>
            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-slate-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-500">Conversion Rate</p>
              <p class="text-3xl font-bold text-slate-900 mt-2">3.24%</p>
              <p class="text-sm text-emerald-600 mt-2">↑ 5.4% from last month</p>
            </div>
            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200">
          <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
        </div>
        <div class="divide-y divide-slate-200">
          <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">New user registered</p>
                  <p class="text-xs text-slate-500 mt-1">sarah.jones@example.com joined the platform</p>
                </div>
              </div>
              <span class="text-xs text-slate-400">2 minutes ago</span>
            </div>
          </div>

          <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">Report generated</p>
                  <p class="text-xs text-slate-500 mt-1">Monthly analytics report is ready</p>
                </div>
              </div>
              <span class="text-xs text-slate-400">15 minutes ago</span>
            </div>
          </div>

          <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-900">System maintenance scheduled</p>
                  <p class="text-xs text-slate-500 mt-1">Scheduled for Sunday, 2:00 AM - 4:00 AM</p>
                </div>
              </div>
              <span class="text-xs text-slate-400">1 hour ago</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- User CRUD Modal -->
<div id="userModal" class="modal">
  <div class="modal-box max-w-4xl bg-white">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-slate-900">Manage Users</h3>
      <button onclick="closeModal()" class="btn btn-sm btn-circle btn-ghost">✕</button>
    </div>

    <!-- Table -->
    <div class="bg-slate-50 rounded-lg border border-slate-200 overflow-hidden mb-6">
      <div class="overflow-x-auto">
        <table class="table w-full">
          <thead class="bg-slate-100">
            <tr>
              <th class="text-slate-700 font-semibold">Name</th>
              <th class="text-slate-700 font-semibold">Email</th>
              <th class="text-slate-700 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="userTableBody" class="bg-white">
            <!-- Filled by JS -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <div class="bg-slate-50 rounded-lg border border-slate-200 p-6">
      <h4 class="text-sm font-semibold text-slate-700 mb-4">Add New User</h4>
      <form id="userForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" id="userId" />
        <div>
          <label class="text-xs font-medium text-slate-600 mb-2 block">Full Name</label>
          <input type="text" id="name" placeholder="Enter full name" class="input input-bordered w-full bg-white" required />
        </div>
        <div>
          <label class="text-xs font-medium text-slate-600 mb-2 block">Email Address</label>
          <input type="email" id="email" placeholder="Enter email address" class="input input-bordered w-full bg-white" required />
        </div>
        <div class="col-span-full flex gap-2">
          <button type="submit" class="btn bg-slate-900 hover:bg-slate-800 text-white border-none">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Save User
          </button>
          <button type="button" onclick="resetForm()" class="btn btn-ghost">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <div class="modal-backdrop bg-slate-900/50" onclick="closeModal()"></div>
</div>

@push('scripts')
<script>
  function openModal() {
    document.getElementById('userModal').classList.add('modal-open');
    fetchUsers();
  }
  
  function closeModal() {
    document.getElementById('userModal').classList.remove('modal-open');
    resetForm();
  }
  
  function resetForm() {
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
  }
  
  function fetchUsers() {
    // Mock data - replace with actual API call
    const mockUsers = [
      { id: 1, name: 'John Doe', email: 'john@example.com' },
      { id: 2, name: 'Jane Smith', email: 'jane@example.com' },
      { id: 3, name: 'Mike Johnson', email: 'mike@example.com' }
    ];
    
    let rows = '';
    mockUsers.forEach(u => {
      rows += `
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="font-medium text-slate-900">${u.name}</td>
          <td class="text-slate-600">${u.email}</td>
          <td class="text-right">
            <button class="btn btn-xs bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200 mr-2" onclick="editUser(${u.id}, '${u.name}', '${u.email}')">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit
            </button>
            <button class="btn btn-xs bg-red-50 hover:bg-red-100 text-red-600 border-red-200" onclick="deleteUser(${u.id})">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Delete
            </button>
          </td>
        </tr>
      `;
    });
    document.getElementById('userTableBody').innerHTML = rows;
  }
  
  function editUser(id, name, email) {
    document.getElementById('userId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('email').value = email;
    document.getElementById('name').focus();
  }
  
  function deleteUser(id) {
    if(confirm('Are you sure you want to delete this user?')) {
      // Replace with actual API call: fetch('/api/users/' + id, { method: 'DELETE' })
      alert('User ' + id + ' deleted successfully');
      fetchUsers();
    }
  }
  
  document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('userId').value;
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    
    // Replace with actual API call
    if(id) {
      alert('User updated: ' + name);
    } else {
      alert('New user created: ' + name);
    }
    
    resetForm();
    fetchUsers();
  });
  
  document.getElementById('openUserModal').addEventListener('click', function(e) {
    e.preventDefault();
    openModal();
  });
</script>
@endpush
</x-app-layout>