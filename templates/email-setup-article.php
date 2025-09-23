<div class="max-w-6xl mx-auto space-y-8">
    <!-- Security Information -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Important Security Information</h2>
        <div class="space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <p class="text-gray-700"><strong>Default Password:</strong></p>
                <div class="flex items-center">
                    <span id="password-display" class="font-mono bg-gray-100 px-2 py-1 rounded">••••••••</span>
                    <button id="toggle-password" class="ml-2 p-1 text-gray-500 hover:text-gray-700">
                        <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <p class="text-gray-600 text-sm"><em>Shared across all staff accounts</em></p>
            <p class="text-gray-700"><strong>Security Reminder:</strong> Change your password immediately after initial setup for better account security.</p>
        </div>
    </div>

    <!-- Web Access -->
    <div class="bg-slate-50 rounded-xl p-8 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Web Access</h2>
        <p class="text-gray-700 mb-4">Access email through your browser:</p>
        
        <div class="bg-white p-4 rounded-lg mb-4 border border-gray-200">
            <p class="font-semibold text-gray-900 mb-2">Webmail URL:</p>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between w-full bg-white border rounded-lg px-3 py-2 gap-2">
                <code class="text-lg break-all">webmail.jjosephsalon.com</code>
                <button onclick="copyToClipboard('webmail.jjosephsalon.com')" class="flex-shrink-0 p-1 text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Setup Steps:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open your web browser</li>
                    <li>Navigate to the webmail URL above</li>
                    <li>Enter your email credentials</li>
                </ol>
                <ol class="list-decimal list-inside space-y-2 text-gray-700" style="counter-reset: list-counter 3;">
                    <li style="counter-increment: list-counter; display: list-item;" value="4">Click 'Login' to access your account</li>
                    <li style="counter-increment: list-counter; display: list-item;" value="5">Select 'Roundcube' to open your mailbox</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- iPhone Setup -->
    <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">iPhone Setup</h2>
        <p class="text-gray-700 mb-4"><strong>IMAP Configuration</strong></p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Incoming Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Incoming Mail - IMAP</h3>
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium text-gray-900">Server</span>
                            <button onclick="copyToClipboard('server.jjosephsalon.com')" class="p-1 text-gray-500 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <code class="text-sm text-gray-700 break-all">server.jjosephsalon.com</code>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Port</span>
                            <div class="text-gray-700 mt-1">993</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Security</span>
                            <div class="text-gray-700 mt-1">SSL/TLS</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Username</span>
                        <div class="text-gray-700 text-sm mt-1">Your email address</div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Password</span>
                        <div class="text-gray-700 text-sm mt-1">Your password</div>
                    </div>
                </div>
            </div>

            <!-- Outgoing Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Outgoing Mail - SMTP</h3>
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium text-gray-900">Server</span>
                            <button onclick="copyToClipboard('server.jjosephsalon.com')" class="p-1 text-gray-500 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <code class="text-sm text-gray-700 break-all">server.jjosephsalon.com</code>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Port</span>
                            <div class="text-gray-700 mt-1">587</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Security</span>
                            <div class="text-gray-700 mt-1">STARTTLS</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Username</span>
                        <div class="text-gray-700 text-sm mt-1">Your email address</div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Password</span>
                        <div class="text-gray-700 text-sm mt-1">Your password</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Setup Steps:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open Settings → Mail → Accounts</li>
                    <li>Tap 'Add Account' → Select 'Other'</li>
                    <li>Choose 'Add Mail Account'</li>
                    <li>Enter your name, email, and password</li>
                </ol>
                <ol class="list-decimal list-inside space-y-2 text-gray-700" style="counter-reset: list-counter 4;">
                    <li style="counter-increment: list-counter; display: list-item;" value="5">Tap 'Next' and select 'IMAP' when prompted</li>
                    <li style="counter-increment: list-counter; display: list-item;" value="6">Configure servers with settings above</li>
                    <li style="counter-increment: list-counter; display: list-item;" value="7">Tap 'Save' to complete setup</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Android Setup -->
    <div class="bg-gray-50 rounded-xl p-8 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Android Setup</h2>
        <p class="text-gray-700 mb-4"><strong>Gmail or Email App Configuration</strong></p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Incoming Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Incoming Mail - IMAP</h3>
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium text-gray-900">Server</span>
                            <button onclick="copyToClipboard('server.jjosephsalon.com')" class="p-1 text-gray-500 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <code class="text-sm text-gray-700 break-all">server.jjosephsalon.com</code>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Port</span>
                            <div class="text-gray-700 mt-1">993</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Security</span>
                            <div class="text-gray-700 mt-1">SSL/TLS</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Username</span>
                        <div class="text-gray-700 text-sm mt-1">Your email address</div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Password</span>
                        <div class="text-gray-700 text-sm mt-1">Your password</div>
                    </div>
                </div>
            </div>

            <!-- Outgoing Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Outgoing Mail - SMTP</h3>
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-medium text-gray-900">Server</span>
                            <button onclick="copyToClipboard('server.jjosephsalon.com')" class="p-1 text-gray-500 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <code class="text-sm text-gray-700 break-all">server.jjosephsalon.com</code>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Port</span>
                            <div class="text-gray-700 mt-1">587</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border border-gray-200">
                            <span class="font-medium text-gray-900 text-sm">Security</span>
                            <div class="text-gray-700 mt-1">STARTTLS</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Username</span>
                        <div class="text-gray-700 text-sm mt-1">Your email address</div>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <span class="font-medium text-gray-900">Password</span>
                        <div class="text-gray-700 text-sm mt-1">Your password</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Setup Steps:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open Email or Gmail app</li>
                    <li>Tap 'Add Account' → Select 'Other'</li>
                    <li>Enter your account details and password</li>
                    <li>Choose 'IMAP' when prompted</li>
                </ol>
                <ol class="list-decimal list-inside space-y-2 text-gray-700" style="counter-reset: list-counter 4;">
                    <li style="counter-increment: list-counter; display: list-item;" value="5">Configure servers with settings above</li>
                    <li style="counter-increment: list-counter; display: list-item;" value="6">Complete the setup process</li>
                    <li style="counter-increment: list-counter; display: list-item;" value="7">Your email will now sync to your device</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Support Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Need Help?</h2>
        <p class="text-gray-700">Contact your manager for technical support with email setup.</p>
    </div>
</div>