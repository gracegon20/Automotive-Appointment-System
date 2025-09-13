<?php
 include 'connect.php';
 session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="icon" type="image/png" href="Images/carlogo.png">
    <link rel="shortcut icon" type="image/png" href="Images/carlogo.png">
    <link rel="stylesheet" href="css/customerstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .autofix-contents {
        align-items: center;
        margin-left: 200px;
        margin-top: 50px;
    }

    .card-contents {
       padding: 50px;
       color: black;


    }
    .autofix-card {
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    padding: 25px;
    margin-bottom: 25px;
    max-width: 960px;
    margin-left: 150px;
    margin-right: auto;
}

.autofix-card h2 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.autofix-card ul {
    list-style: none;
    padding-left: 0;
}

.autofix-card ul li::before {
    content: "🔧 ";
    margin-right: 5px;
}

.cta-button {
    display: inline-block;
    background-color: #007BFF;
    color: white;
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.cta-button:hover {
    background-color: #0056b3;
}

.services-scroll-container {
    overflow-x: auto;
    white-space: nowrap;
    padding: 10px 0;
}

.services-scroll-list {
    display: inline-flex;
    gap: 15px;
}

.scroll-card {
    display: inline-block;
    width: 200px;  /* Adjusted width */
    margin: 10px;
    padding: 20px;  /* Adjusted padding */
    text-align: center;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: #333;
    height: auto;  
    min-height: 280px; 
    box-sizing: border-box;  
    flex-direction: column;
    justify-content: space-between;  
}

.scroll-card img {
    height: 120px;  
    width: 160px;
    border-radius: 4px;
    margin-bottom: 10px; 
}

.scroll-card h3 {
    font-size: 1.1em;
    margin: 10px 0;
}

.scroll-card p {
    font-size: 0.9em;
    color: black;
    margin-bottom: 10px;  
    flex-grow: 1;  
    overflow: hidden;  
    text-overflow: ellipsis;  
    line-height: 1.5em; 
}

.price {
    font-size: 1.2em;
    font-weight: bold;
    color: #2ecc71;
    margin-top: 10px;
}

.scroll-card:hover{
   background-color: aquamarine;
}

</style>
</head>
<body>
    <header class="main-header" style="top: 0;">
        <div class="header-content">
            <div class="logo-container">
                <img src="Images/FrontEndPictures/logo.webp" alt="Auto Repair Shop Logo" class="logo">
                <h2>AutoFIX</h2>
            </div>
        </div>
    </header>
    <div class="sidebar" aria-label="Customer Dashboard Sidebar">
        <ul class="sidebar-menu">
            <li><a href="customerdashboard.php" class="nav-link" aria-label="Dashboard" data-section="dashboard"> <i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <hr>
            <p>Menu</p>
            <li><a href="customer.php" class="nav-link" aria-label="Appointments" data-section="appointments"> <i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="repairtracking.php" class="nav-link" aria-label="Repair Tracking"><i class="fas fa-tools"></i> Repair Tracking</a></li>
            <li><a href="invoices.php" class="nav-link" aria-label="Invoicing" data-section="invoicing"><i class="fas fa-file-invoice"></i> Invoices</a></li>
            <li><a href="history.php" class="nav-link" aria-label="History" data-section="history"><i class="fas fa-history"></i> History</a></li>
            <li><a href="profile.php" class="nav-link" aria-label="Profile Management" data-section="profileManagement"><i class="fas fa-user-cog"></i> Profile</a></li>
            <hr>
            <li><a href="AutoFIX.php" class="nav-link" aria-label="Autofix" data-section="autofix"><i class="fa-solid fa-shop"></i>About AutoFIX</a></li>
            <li><a href="logout.php" class="nav-link" aria-label="Logout" data-section="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>  
        </ul>
    </div>

    <main>
        <div class="autofix-contents">
            <h1>AutoFIX</h1>
            <p>Learn more about our shop below.</p>
        </div>

        <section class="card-contents">

        <div class="autofix-card">
    <h2>🚗 Services We Offer</h2>
    <div class="services-scroll-container">
        <div class="services-scroll-list">
            <div class="scroll-card">
                <img src="Images/FrontEndPictures/oil.webp" alt="Change Oil Service">
                <h3>Change Oil</h3>
                <p class="price">₱400</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/overhaul.jpg" alt="Engine Overhaul">
                <h3>Overhaul</h3>
               <p class="price">₱2500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/wiring.webp" alt="Electrical Wiring">
                <h3>Wiring</h3>
                <p class="price">₱250</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/decals.png" alt="Custom Decals">
                <h3>Decals</h3>
             <p class="price">₱450</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/reset.jpg" alt="System Reset">
                <h3>Reset</h3>
                 <p class="price">₱2500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/retune.jpg" alt="Engine Tuning">
                <h3>Re-tune</h3>
                <p class="price">₱2300</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/tire.webp" alt="Tire Change">
                <h3>Tire Change</h3>
                <p class="price">₱50</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/cvt.jpg" alt="CVT Cleaning">
                <h3>CVT Cleaning</h3>
                <p class="price">₱1500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/headworks.jpg" alt="Headworks Service">
                <h3>Headworks</h3>
                <p class="price">₱500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/rebuild.jpg" alt="Rebuild Service">
                <h3>Rebuild</h3>
                <p class="price">₱2800</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/speedshop.jpg" alt="Speed Shop Service">
                <h3>Speed Shop</h3>
                <p class="price">₱2000</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/suspension.jpg" alt="Suspension Repack Service">
                <h3>Suspension Repack</h3>
                <p class="price">₱1500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/repaint.jpg" alt="Repaint Service">
                <h3>Repaint</h3>
                <p class="price">₱3500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/boreupgrade.jpg" alt="Bore Upgrade Service">
                <h3>Bore Upgrade</h3>
                <p class="price">₱1800</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/brake.webp" alt="Brake Repair Service">
                <h3>Brake Repair</h3>
                <p class="price">₱1100</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/transmission.jpg" alt="Transmission Repair Service">
                <h3>Transmission Repair</h3>
                <p class="price">₱4500</p>
            </div>

            <div class="scroll-card">
                <img src="Images/FrontEndPictures/battery.jpg" alt="Battery Replacement Service">
                <h3>Battery Replacement</h3>
                 <p class="price">₱350</p>
            </div>
        </div>
    </div>
</div>


    <div class="autofix-card">
    <h2>⏰ Working Hours</h2>
    <table cellpadding="20" style="width: 100%; border-collapse: collapse; border: 1px solid #ccc;">
        <thead style="background-color: #f4f4f4;">
            <tr>
                <th style="text-align: left; border: 1px solid #ccc;">Day</th>
                <th style="text-align: left; border: 1px solid #ccc;">Opening Time</th>
                <th style="text-align: left; border: 1px solid #ccc;">Closing Time</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #ccc;">Monday - Friday</td>
                <td style="border: 1px solid #ccc;">8:00 AM</td>
                <td style="border: 1px solid #ccc;">6:00 PM</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ccc;">Saturday</td>
                <td style="border: 1px solid #ccc;">9:00 AM</td>
                <td style="border: 1px solid #ccc;">4:00 PM</td>
            </tr>
            <tr>
                <td style="border: 1px solid #ccc;">Sunday</td>
                <td colspan="2" style="text-align: center; border: 1px solid #ccc;">Closed</td>
            </tr>
        </tbody>
    </table>
</div>


    <div class="autofix-card">
        <h2>📍 More Information</h2>
        <p><strong>Address:</strong> Del Carmen Weste, Balilihan, Bohol, Philippines</p>
        <p><strong>Phone:</strong> 091001256811</p>
        <p><strong>Email:</strong> <a href="#">autofix@gmail.com</a></p>
        <strong>Follow us:</strong> 
          <p><i class="fab fa-facebook"></i> AutoFix Facebook Page</p>
          <p><i class="fab fa-instagram"></i> AutoFix </p>
          <p> <i class="fab fa-twitter"></i> Autofix</p>
        </p>
      </div>

      <div class="autofix-card">
        <h2>Location</h2>
        <iframe 
         src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d983.2814849442956!2d124.04479086954058!3d9.735889396877662!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa15851ee0c5e7%3A0xc99259f24b27ec66!2sDel%20Carmen%2C%20Balilihan%2C%20Bohol%2C%20Philippines!5e0!3m2!1sen!2sph!4v1714541803470!5m2!1sen!2sph" 
         width="100%" 
         height="400" 
         style="border:0;" 
         allowfullscreen="" 
         loading="lazy" 
         referrerpolicy="no-referrer-when-downgrade">
       </iframe>
      </div>
</section>

    </main>
</body>
</html>
