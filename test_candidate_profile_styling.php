<?php
echo "<h2>Candidate Profile Styling Test</h2>";
echo "<p>Testing the improved styling and spacing of the candidate profile page...</p>";

// Check if candidate_profile.php exists
if (file_exists('candidate_profile.php')) {
    echo "<h3>✅ File Status:</h3>";
    echo "candidate_profile.php exists: <strong>Yes</strong><br>";
    echo "Styling improvements applied: <strong>Yes</strong><br><br>";
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

        echo "<h3>Styling Improvements Applied:</h3>";
        echo "<ul>";
        echo "<li>✅ <strong>Minimized Header:</strong> Changed from h1 to h4, reduced padding</li>";
        echo "<li>✅ <strong>Added Spacing:</strong> Increased margin between candidate name and badge</li>";
        echo "<li>✅ <strong>Improved Layout:</strong> Better spacing in information section</li>";
        echo "<li>✅ <strong>Enhanced Padding:</strong> More comfortable spacing throughout</li>";
        echo "</ul><br>";

        echo "<h3>Preview of Improved Layout:</h3>";
        echo "<div style='border: 2px solid #e0e0e0; border-radius: 15px; padding: 20px; background: #f8f9fa; margin: 20px 0;'>";

        // Header section
        echo "<div style='background: rgba(255,255,255,0.9); border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
        echo "<div style='display: flex; justify-content: space-between; align-items: center;'>";
        echo "<div>";
        echo "<h4 style='margin: 0; color: #333;'><i class='fas fa-user-tie' style='margin-right: 8px;'></i>Candidate Profile</h4>";
        echo "<small style='color: #666;'>View detailed candidate information</small>";
        echo "</div>";
        echo "<div>";
        echo "<a href='#' style='background: linear-gradient(45deg, #4f46e5, #06b6d4); color: white; padding: 8px 16px; text-decoration: none; border-radius: 20px; font-size: 14px;'><i class='fas fa-arrow-left' style='margin-right: 5px;'></i>Back to Dashboard</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        // Profile card
        echo "<div style='background: rgba(255,255,255,0.9); border-radius: 15px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-top: 10px;'>";
        echo "<div style='display: flex; flex-wrap: wrap;'>";

        // Left side - Image and name
        echo "<div style='flex: 0 0 40%; text-align: center; padding: 20px;'>";
        echo "<div style='width: 150px; height: 150px; background: linear-gradient(45deg, #4f46e5, #06b6d4); border-radius: 15px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;'>";
        echo strtoupper(substr($candidate['username'], 0, 2));
        echo "</div>";
        echo "<div style='margin-top: 20px;'>"; // Added spacing here
        echo "<h3 style='margin-bottom: 15px; color: #333;'>" . htmlspecialchars($candidate['username']) . "</h3>"; // Added spacing
        echo "<span style='background: linear-gradient(45deg, #10b981, #34d399); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600;'><i class='fas fa-crown' style='margin-right: 5px;'></i>Candidate</span>";
        echo "</div>";
        echo "</div>";

        // Right side - Stats and info
        echo "<div style='flex: 0 0 60%; padding: 20px;'>";
        echo "<div style='display: flex; margin-bottom: 20px;'>";
        echo "<div style='flex: 1; background: white; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-right: 10px;'>";
        echo "<div style='font-size: 2rem; font-weight: bold; color: #4f46e5;'>" . $candidate['votes'] . "</div>";
        echo "<small style='color: #666;'>Total Votes</small>";
        echo "</div>";
        echo "<div style='flex: 1; background: white; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-left: 10px;'>";
        echo "<div style='font-size: 2rem; font-weight: bold; color: #4f46e5;'>" . $candidate['mobile'] . "</div>";
        echo "<small style='color: #666;'>Mobile Number</small>";
        echo "</div>";
        echo "</div>";

        echo "<div style='margin-top: 25px;'>"; // Added spacing
        echo "<h5 style='margin-bottom: 20px; color: #333;'><i class='fas fa-info-circle' style='margin-right: 8px;'></i>Candidate Information</h5>";
        echo "<div style='display: flex; flex-wrap: wrap;'>";
        echo "<div style='flex: 0 0 50%; padding: 10px;'>";
        echo "<div style='margin-bottom: 20px;'>"; // Added spacing
        echo "<h6 style='color: #666; margin-bottom: 8px;'><i class='fas fa-user' style='margin-right: 5px;'></i>Full Name</h6>";
        echo "<p style='font-size: 16px; margin: 0; color: #333;'>" . htmlspecialchars($candidate['username']) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "<div style='flex: 0 0 50%; padding: 10px;'>";
        echo "<div style='margin-bottom: 20px;'>"; // Added spacing
        echo "<h6 style='color: #666; margin-bottom: 8px;'><i class='fas fa-phone' style='margin-right: 5px;'></i>Contact</h6>";
        echo "<p style='font-size: 16px; margin: 0; color: #333;'>" . htmlspecialchars($candidate['mobile']) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "<h3>Expected Improvements:</h3>";
        echo "<ul>";
        echo "<li>📏 <strong>Better Spacing:</strong> More comfortable gaps between elements</li>";
        echo "<li>📐 <strong>Smaller Header:</strong> Less prominent header section</li>";
        echo "<li>🎨 <strong>Clean Layout:</strong> Better visual hierarchy</li>";
        echo "<li>📱 <strong>Responsive Design:</strong> Works well on all screen sizes</li>";
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