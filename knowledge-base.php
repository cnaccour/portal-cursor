<?php
require __DIR__.'/includes/auth.php';
// Knowledge base is accessible to everyone - no login required

require __DIR__.'/includes/header.php';
?>

<style>
.kb-card {
    transition: all 0.2s ease;
}
.kb-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.kb-category {
    border-left: 4px solid #AF831A;
}
.kb-search:focus {
    outline: none;
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}
</style>

<div class="mb-8">
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-3 mb-4">
            <div class="p-3 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-3">Knowledge Base</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Find answers to common questions, learn about salon policies, and access helpful guides for team members.
        </p>
    </div>

    <!-- Search Bar -->
    <div class="max-w-xl mx-auto mb-12">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" 
                   placeholder="Search knowledge base..." 
                   class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl kb-search text-lg"
                   x-data=""
                   x-on:input="searchKnowledgeBase($event.target.value)">
        </div>
    </div>
</div>

<!-- Knowledge Base Categories -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
    
    <!-- Salon Policies -->
    <div class="kb-card bg-white rounded-xl border border-gray-200 shadow-sm p-6 kb-category">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">Salon Policies</h2>
        </div>
        <div class="space-y-3">
            <a href="#time-off-policy" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Time Off & Vacation Policy</h3>
                <p class="text-sm text-gray-600">Learn about blackout dates, compensation days, and request procedures.</p>
            </a>
            <a href="#attendance-policy" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Attendance & Punctuality</h3>
                <p class="text-sm text-gray-600">Guidelines for shift schedules, tardiness, and absences.</p>
            </a>
            <a href="#dress-code" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Dress Code & Appearance</h3>
                <p class="text-sm text-gray-600">Professional appearance standards and uniform requirements.</p>
            </a>
        </div>
    </div>

    <!-- Procedures & Training -->
    <div class="kb-card bg-white rounded-xl border border-gray-200 shadow-sm p-6 kb-category">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">Procedures & Training</h2>
        </div>
        <div class="space-y-3">
            <a href="#client-service" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Client Service Standards</h3>
                <p class="text-sm text-gray-600">Best practices for greeting, consultation, and customer satisfaction.</p>
            </a>
            <a href="#safety-protocols" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Safety & Sanitation Protocols</h3>
                <p class="text-sm text-gray-600">Health department requirements and safety procedures.</p>
            </a>
            <a href="#opening-closing" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Opening & Closing Procedures</h3>
                <p class="text-sm text-gray-600">Daily checklist items and responsibilities by shift.</p>
            </a>
        </div>
    </div>

    <!-- Systems & Technology -->
    <div class="kb-card bg-white rounded-xl border border-gray-200 shadow-sm p-6 kb-category">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">Systems & Technology</h2>
        </div>
        <div class="space-y-3">
            <a href="#pos-system" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">POS System Guide</h3>
                <p class="text-sm text-gray-600">How to process payments, appointments, and inventory.</p>
            </a>
            <a href="#scheduling-software" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Scheduling Software</h3>
                <p class="text-sm text-gray-600">Managing appointments, availability, and client bookings.</p>
            </a>
            <a href="#team-portal" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Team Portal Usage</h3>
                <p class="text-sm text-gray-600">How to use this portal for requests, announcements, and reporting.</p>
            </a>
        </div>
    </div>

    <!-- FAQ & Troubleshooting -->
    <div class="kb-card bg-white rounded-xl border border-gray-200 shadow-sm p-6 kb-category">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-orange-100 rounded-lg">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">FAQ & Troubleshooting</h2>
        </div>
        <div class="space-y-3">
            <a href="#common-questions" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Common Questions</h3>
                <p class="text-sm text-gray-600">Frequently asked questions from team members.</p>
            </a>
            <a href="#technical-issues" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Technical Issues</h3>
                <p class="text-sm text-gray-600">Troubleshooting guides for common system problems.</p>
            </a>
            <a href="#contact-support" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors">
                <h3 class="font-medium text-gray-900 mb-1">Contact Support</h3>
                <p class="text-sm text-gray-600">When and how to reach out for additional help.</p>
            </a>
        </div>
    </div>
</div>

<!-- Featured Articles -->
<div class="bg-gray-50 rounded-xl p-8 mb-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Featured Articles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <article class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg mb-4 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Understanding Blackout Dates</h3>
            <p class="text-gray-600 text-sm mb-4">Learn about holiday blackout periods and how they affect time off requests.</p>
            <a href="#blackout-dates" class="text-sm font-medium" style="color: #AF831A;">Read more →</a>
        </article>
        
        <article class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-lg mb-4 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">New Team Member Guide</h3>
            <p class="text-gray-600 text-sm mb-4">Everything new hires need to know for their first week at J. Joseph Salon.</p>
            <a href="#new-member-guide" class="text-sm font-medium" style="color: #AF831A;">Read more →</a>
        </article>
        
        <article class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg mb-4 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Safety & Sanitation Best Practices</h3>
            <p class="text-gray-600 text-sm mb-4">Essential protocols to maintain a safe and clean environment for everyone.</p>
            <a href="#safety-practices" class="text-sm font-medium" style="color: #AF831A;">Read more →</a>
        </article>
    </div>
</div>

<!-- Quick Links -->
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Links</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="forms/time-off-request.php" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Request Time Off</span>
        </a>
        <a href="announcements.php" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Announcements</span>
        </a>
        <a href="forms.php" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">All Forms</span>
        </a>
        <a href="dashboard.php" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Dashboard</span>
        </a>
    </div>
</div>

<script>
function searchKnowledgeBase(query) {
    // Simple client-side search functionality
    const searchTerm = query.toLowerCase().trim();
    const articles = document.querySelectorAll('.kb-card a, article');
    
    if (searchTerm === '') {
        // Show all articles when search is empty
        articles.forEach(article => {
            article.style.display = 'block';
            article.parentElement.style.display = 'block';
        });
        return;
    }
    
    articles.forEach(article => {
        const title = article.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = article.querySelector('p')?.textContent.toLowerCase() || '';
        const content = title + ' ' + description;
        
        if (content.includes(searchTerm)) {
            article.style.display = 'block';
            if (article.parentElement.classList.contains('kb-card')) {
                article.parentElement.style.display = 'block';
            }
        } else {
            article.style.display = 'none';
        }
    });
}
</script>

<?php require __DIR__.'/includes/footer.php'; ?>