<?php
/**
 * Combined Announcements Loader
 * Loads both static and dynamic announcements
 * 
 * @deprecated Use loadAllAnnouncements() from announcement-helpers.php instead
 */

require_once __DIR__.'/announcement-helpers.php';

// Use the improved loader function
return loadAllAnnouncements();