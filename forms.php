<?php
require __DIR__.'/includes/auth.php';
// Forms are accessible to all users - no login required
require __DIR__.'/includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Forms & Submissions</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Submit shift reports, request time off, and access other important forms for team operations.
        </p>
    </div>

    <!-- Forms Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        
        <!-- Shift Report Card -->
        <div class="group relative bg-white rounded-2xl border border-gray-200 hover:border-gray-300 hover:shadow-lg transition-all duration-300 overflow-hidden">
            <!-- Status Badge -->
            <div class="absolute top-4 right-4 z-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Sign-in required
                </span>
            </div>
            
            <!-- Card Content -->
            <div class="p-8 h-full flex flex-col">
                <!-- Icon & Title -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Shift Report</h3>
                        <p class="text-sm text-gray-500">Daily Operations</p>
                    </div>
                </div>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6 flex-grow">
                    Complete your daily shift checklist and report activities for morning or evening shifts. Track duties, customer reviews, shipments, and any incidents.
                </p>
                
                <!-- Features -->
                <div class="mb-6 space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Daily checklist tracking</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Customer review logging</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Incident reporting</span>
                    </div>
                </div>
                
                <!-- Action Button -->
                <a href="forms/shift-reports.php" 
                   class="inline-flex items-center justify-center gap-2 w-full px-6 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all duration-200 font-medium group-hover:bg-gray-800">
                    <span>Create Shift Report</span>
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Time Off Request Card -->
        <div class="group relative bg-white rounded-2xl border border-gray-200 hover:border-gray-300 hover:shadow-lg transition-all duration-300 overflow-hidden">
            <!-- Status Badge -->
            <div class="absolute top-4 right-4 z-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Open access
                </span>
            </div>
            
            <!-- Card Content -->
            <div class="p-8 h-full flex flex-col">
                <!-- Icon & Title -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Time Off Request</h3>
                        <p class="text-sm text-gray-500">Personal Time</p>
                    </div>
                </div>
                
                <!-- Description -->
                <p class="text-gray-600 mb-6 flex-grow">
                    Submit requests for vacation days, personal time, sick leave, or other time off needs. Easy scheduling with automatic day calculation and policy acknowledgments.
                </p>
                
                <!-- Features -->
                <div class="mb-6 space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Smart date range picker</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Multiple time off types</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        <span>Automatic day calculation</span>
                    </div>
                </div>
                
                <!-- Action Button -->
                <a href="forms/time-off-request.php" 
                   class="inline-flex items-center justify-center gap-2 w-full px-6 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all duration-200 font-medium group-hover:bg-gray-800">
                    <span>Request Time Off</span>
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-8 border border-gray-200">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Need Help?</h3>
            <p class="text-gray-600 mb-4 max-w-md mx-auto">
                If you have questions about filling out these forms or need assistance with submissions, contact your manager or HR representative.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-lg border text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email Support
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-lg border text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Call Manager
                </span>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>