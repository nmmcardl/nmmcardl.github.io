<?php
/**********************************************
 * DATABASE CONNECTION
 **********************************************/

$host = "localhost";
$user = "root";
$pass = "pass";
$db   = "paint_contracting"; // <-- CHANGE THIS

$conn = new mysqli($host, $user, $pass, $db);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

/**********************************************
 * HELPER: CLEAN INPUT
 **********************************************/
function clean($data) {
    return htmlspecialchars(trim($data));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Business Dashboard</title>

<style>
body {
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f4f4;
}

/* LEFT SIDE NAVIGATION */
#sidebar {
    position:fixed;
    width:220px;
    height:100%;
    background:#222;
    color:white;
    padding-top:20px;
}

#sidebar h2 {
    text-align:center;
    margin-bottom:20px;
}

#sidebar a {
    display:block;
    padding:12px;
    color:white;
    text-decoration:none;
    border-bottom:1px solid #333;
}

#sidebar a:hover {
    background:#444;
}

#content {
    margin-left:230px;
    padding:25px;
}

/* FORMS */
form {
    background:white;
    padding:15px;
    border-radius:8px;
    box-shadow:0 0 5px rgba(0,0,0,0.3);
    max-width:600px;
}

input, select {
    width:100%;
    padding:8px;
    margin:8px 0;
    border-radius:5px;
    border:1px solid #ccc;
}

button {
    padding:10px 20px;
    background:#2a6edb;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-weight:bold;
}

button:hover {
    background:#1b4ea3;
}

.table-box {
    background:white;
    border-radius:8px;
    padding:15px;
    margin-top:20px;
    box-shadow:0 0 5px rgba(0,0,0,0.3);
}

table {
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

table th, table td {
    border:1px solid #ccc;
    padding:8px;
    text-align:left;
}

table th {
    background:#eee;
}

.section-title {
    font-size:22px;
    margin-bottom:15px;
    font-weight:bold;
}
</style>

</head>
<body>

<!-- SIDEBAR NAVIGATION -->
<div id="sidebar">
    <h2>Dashboard</h2>
    <a href="?page=jobs">Jobs</a>
    <a href="?page=clients">Clients</a>
    <a href="?page=add_job">➕ Add Job</a>
    <a href="?page=add_client">➕ Add Client</a>
</div>

<div id="content">
<?php
$page = $_GET['page'] ?? '';

/**********************************************
 * JOB SEARCH SCREEN
 **********************************************/
if($page == "jobs"){
?>
    <div class="section-title">Search Jobs</div>

    <form method="POST">
        <label>Search by Job ID or Customer Name:</label>
        <input type="text" name="job_search" required>
        <button type="submit">Search</button>
    </form>

<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $search = clean($_POST["job_search"]);

        $sql = "
        SELECT J.job_id, J.hours, J.address,
               C.Fname, C.Lname, C.sq_footage
        FROM JOBS J, CUSTOMER C
        WHERE J.customer_id = C.customer_id
        AND (J.job_id = '$search'
         OR C.Fname LIKE '%$search%'
         OR C.Lname LIKE '%$search%')";
        
        $result = $conn->query($sql);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo "<div class='table-box'>";
                echo "<h3>Job ID: {$row['job_id']}</h3>";
                echo "Address: {$row['address']}<br>";
                echo "Hours: {$row['hours']}<br>";
                echo "Customer: {$row['Fname']} {$row['Lname']}<br>";
                echo "Square Footage: {$row['sq_footage']}<br><br>";

                echo "<strong>Workers on Job:</strong>";
                $jobid = $row['job_id'];

                $w = $conn->query("
                    SELECT W.name, H.hours
                    FROM HOURS H, WORKER W
                    WHERE H.emp_id = W.emp_id
                    AND H.job_id = '$jobid'
                ");
                if($w->num_rows>0){
                    echo "<table><tr><th>Name</th><th>Hours Worked</th></tr>";
                    while($r=$w->fetch_assoc()){
                        echo "<tr><td>{$r['name']}</td><td>{$r['hours']}</td></tr>";
                    }
                    echo "</table>";
                } else echo "<br>No workers logged.<br>";

                echo "<br><strong>Supplies Used:</strong>";

                $s = $conn->query("
                    SELECT P.name, P.brand, P.cost
                    FROM PRODUCTS_USED U, PRODUCT_DETAILS P
                    WHERE U.product_id = P.product_id
                    AND U.job_id = '$jobid'
                ");

                $totalCost = 0;

                if($s->num_rows > 0){
                    echo "<table><tr><th>Product</th><th>Brand</th><th>Cost</th></tr>";
                    while($r=$s->fetch_assoc()){
                        echo "<tr><td>{$r['name']}</td><td>{$r['brand']}</td><td>\${$r['cost']}</td></tr>";
                        $totalCost += $r['cost'];
                    }
                    echo "</table>";
                    echo "<strong>Total Supply Cost: \${$totalCost}</strong>";
                } else echo "<br>No supplies used.";

                echo "</div>";
            }
        }
    }
}

/**********************************************
 * CLIENT SEARCH SCREEN
 **********************************************/
else if($page == "clients"){
?>
    <div class="section-title">Search Clients</div>

    <form method="POST">
        <label>Search by Client ID or Name:</label>
        <input type="text" name="client_search" required>
        <button type="submit">Search</button>
    </form>

<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $search = clean($_POST["client_search"]);

        $sql = "
            SELECT *
            FROM CUSTOMER
            WHERE customer_id = '$search'
               OR Fname LIKE '%$search%'
               OR Lname LIKE '%$search%'
        ";

        $res = $conn->query($sql);

        while($c = $res->fetch_assoc()){
            echo "<div class='table-box'>";
            echo "<h3>{$c['Fname']} {$c['Lname']}</h3>";
            echo "Customer ID: {$c['customer_id']}<br>";
            echo "Address: {$c['address']}<br>";
            echo "Square Footage: {$c['sq_footage']}<br><br>";

            echo "<strong>Jobs for this client:</strong>";

            $cid = $c["customer_id"];
            $jobs = $conn->query("
                SELECT *
                FROM JOBS
                WHERE customer_id = '$cid'
            ");

            if($jobs->num_rows>0){
                echo "<table>
                        <tr><th>Job ID</th><th>Hours</th><th>Address</th></tr>";
                while($j=$jobs->fetch_assoc()){
                    echo "<tr>
                            <td>{$j['job_id']}</td>
                            <td>{$j['hours']}</td>
                            <td>{$j['address']}</td>
                          </tr>";
                }
                echo "</table>";
            } else echo "<br>No jobs found.";

            echo "</div>";
        }
    }
}

/**********************************************
 * ADD CLIENT
 **********************************************/
else if($page == "add_client"){
?>
    <div class="section-title">Add New Client</div>

    <form method="POST">
        <label>First Name:</label>
        <input type="text" name="fname" required>

        <label>Last Name:</label>
        <input type="text" name="lname" required>

        <label>Address:</label>
        <input type="text" name="address" required>

        <label>Square Footage:</label>
        <input type="number" name="sq" required>

        <button type="submit">Add Client</button>
    </form>

<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $fname = clean($_POST["fname"]);
        $lname = clean($_POST["lname"]);
        $address = clean($_POST["address"]);
        $sq = intval($_POST["sq"]);

        $sql = "INSERT INTO CUSTOMER(Fname, Lname, address, sq_footage, customer_id)
                VALUES('$fname', '$lname', '$address', '$sq', LPAD(
                    (SELECT IFNULL(MAX(customer_id)+1, 1) FROM CUSTOMER), 3, '0'
                ))";

        if($conn->query($sql))
            echo "<p style='color:green;'>Client Added!</p>";
        else
            echo "<p style='color:red;'>Error: ".$conn->error."</p>";
    }
}

/**********************************************
 * ADD JOB
 **********************************************/
else if($page == "add_job"){
?>
    <div class="section-title">Add New Job</div>

    <form method="POST">
        <label>Select Client:</label>
        <select name="cid" required>
            <option value="">--Select--</option>
            <?php
                $c=$conn->query("SELECT * FROM CUSTOMER");
                while($row=$c->fetch_assoc()){
                    echo "<option value='{$row['customer_id']}'>{$row['Fname']} {$row['Lname']} (ID {$row['customer_id']})</option>";
                }
            ?>
        </select>

        <label>Hours:</label>
        <input type="number" name="hours" required>

        <button type="submit">Add Job</button>
    </form>

<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $cid = clean($_POST["cid"]);
        $hours = intval($_POST["hours"]);

        // Get client's address automatically
        $addr = $conn->query("SELECT address FROM CUSTOMER WHERE customer_id='$cid'")
                     ->fetch_assoc()['address'];

        $sql = "
        INSERT INTO JOBS(job_id, hours, address, customer_id)
        VALUES(
            LPAD((SELECT IFNULL(MAX(job_id)+1,1) FROM JOBS), 3, '0'),
            '$hours',
            '$addr',
            '$cid'
        )";

        if($conn->query($sql))
            echo "<p style='color:green;'>Job Added!</p>";
        else
            echo "<p style='color:red;'>Error: ".$conn->error."</p>";
    }
}

?>

</div>
</body>
</html>
