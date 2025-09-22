<div class="max-w-6xl mx-auto space-y-8">
    <!-- Security Information -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Important Security Information</h2>
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <p class="text-gray-700"><strong>Default Password:</strong></p>
                <div class="relative flex items-center">
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
    <div class="bg-white">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Web Access</h2>
        <p class="text-gray-700 mb-4">Access email through your browser:</p>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <p class="font-semibold text-gray-900 mb-2">Webmail URL:</p>
            <div class="flex items-center justify-between w-full bg-white border rounded-lg px-3 py-2">
                <code class="text-lg flex-1">webmail.jjosephsalon.com</code>
                <button onclick="copyToClipboard('webmail.jjosephsalon.com')" class="ml-2 p-1 text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Setup Steps:</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Open your web browser</li>
                <li>Navigate to the webmail URL above</li>
                <li>Enter your email credentials</li>
                <li>Click 'Login' to access your account</li>
                <li>Select 'Roundcube' to open your mailbox</li>
            </ol>
        </div>
    </div>

    <!-- iPhone Setup -->
    <div class="bg-white">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">iPhone Setup</h2>
        <p class="text-gray-700 mb-4"><strong>IMAP Configuration</strong></p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Incoming Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Incoming Mail - IMAP</h3>
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Server</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="flex items-center justify-between">
                                        <code>server.jjosephsalon.com</code>
                                        <button onclick="copyToClipboard('server.jjosephsalon.com')" class="ml-2 p-1 text-gray-500 hover:text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Port</td>
                                <td class="px-4 py-3 text-gray-700">993</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Security</td>
                                <td class="px-4 py-3 text-gray-700">SSL/TLS</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Username</td>
                                <td class="px-4 py-3 text-gray-700">Your email address</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Password</td>
                                <td class="px-4 py-3 text-gray-700">Your password</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Outgoing Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Outgoing Mail - SMTP</h3>
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Server</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="flex items-center justify-between">
                                        <code>server.jjosephsalon.com</code>
                                        <button onclick="copyToClipboard('server.jjosephsalon.com')" class="ml-2 p-1 text-gray-500 hover:text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Port</td>
                                <td class="px-4 py-3 text-gray-700">587</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Security</td>
                                <td class="px-4 py-3 text-gray-700">STARTTLS</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Username</td>
                                <td class="px-4 py-3 text-gray-700">Your email address</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Password</td>
                                <td class="px-4 py-3 text-gray-700">Your password</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Setup Steps:</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Open Settings → Mail → Accounts</li>
                <li>Tap 'Add Account' → Select 'Other'</li>
                <li>Choose 'Add Mail Account'</li>
                <li>Enter your name, email, and password</li>
                <li>Tap 'Next' and select 'IMAP' when prompted</li>
                <li>Configure servers with settings above</li>
                <li>Tap 'Save' to complete setup</li>
            </ol>
        </div>
    </div>

    <!-- Android Setup -->
    <div class="bg-white">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Android Setup</h2>
        <p class="text-gray-700 mb-4"><strong>Gmail or Email App Configuration</strong></p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Incoming Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Incoming Mail - IMAP</h3>
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Server</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="flex items-center justify-between">
                                        <code>server.jjosephsalon.com</code>
                                        <button onclick="copyToClipboard('server.jjosephsalon.com')" class="ml-2 p-1 text-gray-500 hover:text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Port</td>
                                <td class="px-4 py-3 text-gray-700">993</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Security</td>
                                <td class="px-4 py-3 text-gray-700">SSL/TLS</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Username</td>
                                <td class="px-4 py-3 text-gray-700">Your email address</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Password</td>
                                <td class="px-4 py-3 text-gray-700">Your password</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Outgoing Mail -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Outgoing Mail - SMTP</h3>
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Server</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="flex items-center justify-between">
                                        <code>server.jjosephsalon.com</code>
                                        <button onclick="copyToClipboard('server.jjosephsalon.com')" class="ml-2 p-1 text-gray-500 hover:text-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Port</td>
                                <td class="px-4 py-3 text-gray-700">587</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Security</td>
                                <td class="px-4 py-3 text-gray-700">STARTTLS</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">Username</td>
                                <td class="px-4 py-3 text-gray-700">Your email address</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-3 font-medium text-gray-900">Password</td>
                                <td class="px-4 py-3 text-gray-700">Your password</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Setup Steps:</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Open Email or Gmail app</li>
                <li>Tap 'Add Account' → Select 'Other'</li>
                <li>Enter your account details and password</li>
                <li>Choose 'IMAP' when prompted</li>
                <li>Configure servers with settings above</li>
                <li>Complete the setup process</li>
                <li>Your email will now sync to your device</li>
            </ol>
        </div>
    </div>

    <!-- Support Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-3">Need Help?</h2>
        <p class="text-gray-700">Contact your manager for technical support with email setup.</p>
    </div>
</div>

<script>
// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-password');
    const passwordDisplay = document.getElementById('password-display');
    let isPasswordVisible = false;
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            isPasswordVisible = !isPasswordVisible;
            if (isPasswordVisible) {
                passwordDisplay.textContent = 'salon123';
            } else {
                passwordDisplay.textContent = '••••••••';
            }
        });
    }
});

// Copy to clipboard functionality
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const originalText = event.target.closest('button').innerHTML;
        const button = event.target.closest('button');
        
        button.innerHTML = `
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        `;
        
        setTimeout(function() {
            button.innerHTML = originalText;
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy to clipboard');
    });
}
</script>