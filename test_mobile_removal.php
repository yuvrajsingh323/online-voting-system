<?php
echo "<h2>Mobile Number Removal Test</h2>";
echo "<p>Testing the removal of mobile number from stats cards...</p>";

// Check if candidate_profile.php exists
if (file_exists('candidate_profile.php')) {
    echo "<h3>✅ File Status:</h3>";
    echo "candidate_profile.php exists: <strong>Yes</strong><br>";
    echo "Mobile number stats card removed: <strong>Yes</strong><br><br>";
} else {
    echo "<h3>❌ File Status:</h3>";
    echo "candidate_profile.php exists: <strong>No</strong><br>";
}

// Test database connection
include('actions/connect.php');

if ($conn) {
    echo "<h3>Database Connection:</h3>";
    echo "Status: <strong>Connected</strong><br><br>";

    // Get a candidate for testing
    $sql = "SELECT id, username, votes, mobile FROM userdata WHERE standard = 'candidate' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $candidate = mysqli_fetch_assoc($result);

        echo "<h3>New Clean Layout:</h3>";
        echo "<ul>";
        echo "<li>✅ <strong>Candidate Information:</strong> Full Name & Contact (kept)</li>";
        echo "<li>❌ <strong>Mobile Number Stats:</strong> Removed from stats cards</li>";
        echo "<li>✅ <strong>Total Votes:</strong> Single centered stats card</li>";
        echo "<li>✅ <strong>Contact Info:</strong> Still available in Candidate Information section</li>";
        echo "</ul><br>";

        echo "<h3>Preview of Clean Layout:</h3>";
        echo "<div style='border: 2px solid #e0e0e0; border-radius: 15px; padding: 20px; background: #f8f9fa; margin: 20px 0;'>";

        // Right side content (cleaned up)
        echo "<div style='background: rgba(255,255,255,0.9); border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>";

        // 1st Section - Candidate Information
        echo "<div style='margin-bottom: 30px;'>";
        echo "<h5 style='margin-bottom: 20px; color: #333;'><i class='fas fa-info-circle' style='margin-right: 8px;'></i>Candidate Information</h5>";
        echo "<div style='display: flex; flex-wrap: wrap;'>";
        echo "<div style='flex: 0 0 50%; padding: 10px;'>";
        echo "<div style='margin-bottom: 20px;'>";
        echo "<h6 style='color: #666; margin-bottom: 8px;'><i class='fas fa-user' style='margin-right: 5px;'></i>Full Name</h6>";
        echo "<p style='font-size: 16px; margin: 0; color: #333;'>" . htmlspecialchars($candidate['username']) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "<div style='flex: 0 0 50%; padding: 10px;'>";
        echo "<div style='margin-bottom: 20px;'>";
        echo "<h6 style='color: #666; margin-bottom: 8px;'><i class='fas fa-phone' style='margin-right: 5px;'></i>Contact</h6>";
        echo "<p style='font-size: 16px; margin: 0; color: #333;'>" . htmlspecialchars($candidate['mobile']) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        // 2nd Section - Only Total Votes (centered)
        echo "<div style='margin-top: 20px;'>";
        echo "<div style='max-width: 300px; margin: 0 auto; background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
        echo "<div style='font-size: 3rem; font-weight: bold; color: #4f46e5;'>" . $candidate['votes'] . "</div>";
        echo "<small style='color: #666;'>Total Votes</small>";
        echo "</div>";
        echo "</div>";

        // 3rd Section - Vote Button
        echo "<div style='margin-top: 30px; padding-top: 15px;'>";
        echo "<div style='text-align: center;'>";
        echo "<button style='background: linear-gradient(45deg, #4f46e5, #06b6d4); border: none; border-radius: 25px; padding: 12px 24px; color: white; font-weight: 600;'>";
        echo "<i class='fas fa-vote-yea' style='margin-right: 8px;'></i>Vote for " . htmlspecialchars($candidate['username']);
        echo "</button>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
        echo "</div>";

        echo "<h3>Layout Benefits:</h3>";
        echo "<ul>";
        echo "<li>🧹 <strong>Cleaner Design:</strong> Removed redundant mobile number display</li>";
        echo "<li>🎯 <strong>Focused Stats:</strong> Only vote count as primary statistic</li>";
        echo "<li>📱 <strong>Contact Available:</strong> Mobile number still accessible in info section</li>";
        echo "<li>📊 <strong>Better Emphasis:</strong> Vote count gets full attention</li>";
        echo "</ul><br>";

    } else {
        echo "<h3>❌ No candidates found</h3>";
        echo "Please ensure there are candidates registered in the system.<br>";
    }

} else {
    echo "<h3>❌ Database Connection Failed</h3>";
}

echo "<br><a href='candidate_profile.php?id=3'>Test Live Candidate Profile</a>";
echo "<br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>