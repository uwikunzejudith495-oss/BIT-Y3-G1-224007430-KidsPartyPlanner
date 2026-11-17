<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$page = $_GET['page'] ?? 'home';
$pageTitle = 'Kids Party Planner';

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_user(): void {
    if (!is_logged_in()) {
        flash('Please login first.', 'error');
        redirect('index.php?page=login');
    }
}

function page_url(string $page): string {
    return 'index.php?page=' . urlencode($page);
}

function get_party(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare(
        'SELECT * FROM parties WHERE party_id = ? AND parent_id = ?'
    );
    $stmt->execute([$id, current_user_id()]);
    $party = $stmt->fetch();
    return $party ?: null;
}

/* REGISTER */
if ($page === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = post_string('username', 100);
    $email = post_string('email', 150);
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        flash('Enter a username, valid email and password of at least 8 characters.', 'error');
        redirect(page_url('register'));
    }

    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        flash('This email is already registered.', 'error');
        redirect(page_url('register'));
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$username, $email, $hash, 'parent']);

    flash('Registration successful. Please login.');
    redirect(page_url('login'));
}

/* LOGIN */
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = post_string('email', 150);
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['username'] = $user['username'];

        flash('Welcome back, ' . $user['username'] . '!');
        redirect(page_url('dashboard'));
    }

    flash('Incorrect email or password.', 'error');
    redirect(page_url('login'));
}

/* LOGOUT */
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    redirect('index.php');
}

/* CREATE PARTY */
if ($page === 'party_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $child = post_string('child_name', 100);
    $date = post_string('party_date', 20);
    $theme = post_string('theme', 100);
    $budget = filter_var($_POST['budget'] ?? '', FILTER_VALIDATE_FLOAT);

    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    if (
        $child === '' ||
        $theme === '' ||
        $budget === false ||
        $budget < 0 ||
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $date
    ) {
        flash('Please enter valid party information.', 'error');
        redirect(page_url('party_form'));
    }

    $stmt = $pdo->prepare(
        'INSERT INTO parties (parent_id, child_name, party_date, theme, budget)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        current_user_id(),
        $child,
        $date,
        $theme,
        $budget
    ]);

    flash('Birthday party created successfully.');
    redirect(page_url('parties'));
}

/* UPDATE PARTY */
if ($page === 'party_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $id = (int)($_POST['party_id'] ?? 0);
    $child = post_string('child_name', 100);
    $date = post_string('party_date', 20);
    $theme = post_string('theme', 100);
    $budget = filter_var($_POST['budget'] ?? '', FILTER_VALIDATE_FLOAT);

    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

if (!get_party($pdo, $id) || $child === '' || $theme === '' ||
    $budget === false || $budget < 0 ||
    !$dateObject || $dateObject->format('Y-m-d') !== $date) {
        flash('Invalid party information.', 'error');
        redirect(page_url('parties'));
    }

    $stmt = $pdo->prepare(
        'UPDATE parties
         SET child_name=?, party_date=?, theme=?, budget=?
         WHERE party_id=? AND parent_id=?'
    );
    $stmt->execute([$child, $date, $theme, $budget, $id, current_user_id()]);

    flash('Party updated successfully.');
    redirect(page_url('parties'));
}

/* DELETE PARTY */
if ($page === 'party_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $id = (int)($_POST['party_id'] ?? 0);

    if (get_party($pdo, $id)) {
        $stmt = $pdo->prepare(
            'DELETE FROM parties WHERE party_id=? AND parent_id=?'
        );
        $stmt->execute([$id, current_user_id()]);
        flash('Party deleted successfully.');
    } else {
        flash('Party not found.', 'error');
    }

    redirect(page_url('parties'));
}

/* CREATE VENDOR */
if ($page === 'vendor_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $name = post_string('name', 120);
    $service = post_string('service_type', 100);
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $email = post_string('contact_email', 150);

   if ($name === '' || $service === '' || $price === false || $price < 0 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Please enter valid vendor information.', 'error');
        redirect(page_url('vendor_form'));
    }

    $stmt = $pdo->prepare(
        'INSERT INTO vendors (name, service_type, price, contact_email)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $service, $price, $email]);

    flash('Vendor added successfully.');
    redirect(page_url('vendors'));
}

/* UPDATE VENDOR */
if ($page === 'vendor_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $id = (int)($_POST['vendor_id'] ?? 0);
    $name = post_string('name', 120);
    $service = post_string('service_type', 100);
    $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
    $email = post_string('contact_email', 150);

   if ($id < 1 || $name === '' || $service === '' ||
    $price === false || $price < 0 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('Invalid vendor information.', 'error');
    redirect(page_url('vendors'));
}

    $stmt = $pdo->prepare(
        'UPDATE vendors SET name=?, service_type=?, price=?, contact_email=?
         WHERE vendor_id=?'
    );
    $stmt->execute([$name, $service, $price, $email, $id]);

    flash('Vendor updated successfully.');
    redirect(page_url('vendors'));
}

/* DELETE VENDOR */
if ($page === 'vendor_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $id = (int)($_POST['vendor_id'] ?? 0);

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare(
                'DELETE FROM vendors WHERE vendor_id = ?'
            );
            $stmt->execute([$id]);

            flash('Vendor deleted successfully.');
        } catch (PDOException $e) {
            flash('This vendor cannot be deleted because it may have bookings.', 'error');
        }
    } else {
        flash('Invalid vendor ID.', 'error');
    }

    redirect(page_url('vendors'));
}


/* CREATE BOOKING */
if ($page === 'booking_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $partyId = (int)($_POST['party_id'] ?? 0);
    $vendorId = (int)($_POST['vendor_id'] ?? 0);

    $party = get_party($pdo, $partyId);

    $stmt = $pdo->prepare('SELECT * FROM vendors WHERE vendor_id = ?');
    $stmt->execute([$vendorId]);
    $vendor = $stmt->fetch();
    if (!$party || !$vendor) {
        flash('Choose a valid party and vendor.', 'error');
        redirect(page_url('bookings'));
    }

    $stmt = $pdo->prepare(
        'INSERT INTO bookings (party_id, vendor_id, amount, status)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$partyId, $vendorId, $vendor['price'], 'Pending']);

    flash('Booking created successfully.');
    redirect(page_url('bookings'));
}

/* DELETE BOOKING */
if ($page === 'booking_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_user();
    verify_csrf();

    $id = (int)($_POST['booking_id'] ?? 0);

    $stmt = $pdo->prepare(
        'DELETE b FROM bookings b
         INNER JOIN parties p ON b.party_id = p.party_id
         WHERE b.booking_id=? AND p.parent_id=?'
    );
    $stmt->execute([$id, current_user_id()]);

    flash('Booking deleted.');
    redirect(page_url('bookings'));
}

/* LOAD PAGE */
switch ($page) {
    case 'home':
        $pageTitle = 'Home';
        break;

    case 'register':
        $pageTitle = 'Register';
        break;

    case 'login':
        $pageTitle = 'Login';
        break;

    case 'dashboard':
    case 'parties':
    case 'party_form':
    case 'vendors':
    case 'vendor_form':
    case 'bookings':
        require_user();
        $pageTitle = ucfirst(str_replace('_', ' ', $page));
        break;

    default:
        $page = 'home';
}

/* SHOW HEADER */
require __DIR__ . '/includes/header.php';
?>

<?php if ($page === 'home'): ?>

<section class="hero">
    <h1>Make Every Birthday Special!</h1>
    <p>Plan unforgettable birthday parties for your children.</p>
    <a class="btn" href="index.php?page=register">Get Started</a>
</section>

<?php elseif ($page === 'register'): ?>

<section class="panel">
    <h2>Create an Account</h2>
    <form method="POST" action="index.php?page=register">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Username</label>
        <input name="username" required maxlength="100">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password (minimum 8 characters)</label>
        <input type="password" name="password" minlength="8" required>
        <button class="btn">Register</button>
    </form>
    <p>Already registered? <a href="index.php?page=login">Login</a></p>
</section>

<?php elseif ($page === 'login'): ?>

<section class="panel">
    <h2>Login</h2>
    <form method="POST" action="index.php?page=login">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button class="btn">Login</button>
    </form>
    <p>Don't have an account? <a href="index.php?page=register">Register</a></p>
</section>

<?php elseif ($page === 'dashboard'): ?>

<section class="panel">
    <h2>Welcome, <?= e($_SESSION['username'] ?? 'Parent') ?>!</h2>
    <p>Manage your children's birthday parties and bookings.</p>
    <a class="btn" href="index.php?page=parties">My Parties</a>
    <a class="btn" href="index.php?page=vendors">View Vendors</a>
    <a class="btn" href="index.php?page=bookings">My Bookings</a>
</section>

<?php elseif ($page === 'parties'): ?>

<section class="panel">
    <h2>My Birthday Parties</h2>
    <a class="btn" href="index.php?page=party_form">Add New Party</a>
    <?php
    $stmt = $pdo->prepare('SELECT * FROM parties WHERE parent_id=? ORDER BY party_date');
    $stmt->execute([current_user_id()]);
    $parties = $stmt->fetchAll();
    ?>
    <div class="table-wrap">
    <table>
        <tr><th>Child</th><th>Date</th><th>Theme</th><th>Budget</th><th>Actions</th></tr>
        <?php foreach ($parties as $party): ?>
        <tr>
            <td><?= e($party['child_name']) ?></td>
            <td><?= e($party['party_date']) ?></td>
            <td><?= e($party['theme']) ?></td>
            <td><?= e((string)$party['budget']) ?></td>
            <td>
                <a href="index.php?page=party_form&id=<?= (int)$party['party_id'] ?>">Edit</a>
                <form method="POST" action="index.php?page=party_delete" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="party_id" value="<?= (int)$party['party_id'] ?>">
                    <button class="danger" onclick="return confirm('Delete this party?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</section>

<?php elseif ($page === 'party_form'): ?>

<?php
$editParty = null;
if (isset($_GET['id'])) {
    $editParty = get_party($pdo, (int)$_GET['id']);
}
?>
<section class="panel">
    <h2><?= $editParty ? 'Edit Party' : 'Add Birthday Party' ?></h2>
    <form method="POST" action="index.php?page=<?= $editParty ? 'party_update' : 'party_create' ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($editParty): ?>
        <input type="hidden" name="party_id" value="<?= (int)$editParty['party_id'] ?>">
        <?php endif; ?>
        <label>Child's Name</label>
        <input name="child_name" required value="<?= e($editParty['child_name'] ?? '') ?>">
        <label>Party Date</label>
        <input type="date" name="party_date" required value="<?= e($editParty['party_date'] ?? '') ?>">
        <label>Party Theme</label>
        <input name="theme" required value="<?= e($editParty['theme'] ?? '') ?>">
        <label>Budget</label>
        <input type="number" name="budget" min="0" step="0.01" required value="<?= e((string)($editParty['budget'] ?? '')) ?>">
        <button class="btn">Save Party</button>
    </form>
</section>

<?php elseif ($page === 'vendors'): ?>

<section class="panel">
    <h2>Party Vendors</h2>

    <a class="btn" href="index.php?page=vendor_form">Add Vendor</a>

    <label for="vendorSearch">Search Vendors</label>
    <input
        type="search"
        id="vendorSearch"
        placeholder="Search by vendor name or service..."
    >

    <div id="vendorList">
        <?php
        $vendors = $pdo->query(
            'SELECT * FROM vendors ORDER BY name'
        )->fetchAll();
        ?>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($vendors as $vendor): ?>
                <tr>
                    <td><?= e($vendor['name']) ?></td>
                    <td><?= e($vendor['service_type']) ?></td>
                    <td><?= e((string)$vendor['price']) ?></td>
                    <td><?= e($vendor['contact_email']) ?></td>
                    <td>
                        <a href="index.php?page=vendor_form&id=<?= (int)$vendor['vendor_id'] ?>">Edit</a>

                        <form method="POST"
                              action="index.php?page=vendor_delete"
                              class="inline-form">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="vendor_id"
                                   value="<?= (int)$vendor['vendor_id'] ?>">
                            <button class="danger"
                                    onclick="return confirm('Delete this vendor?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</section>

<?php elseif ($page === 'vendor_form'): ?>

<?php
$editVendor = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM vendors WHERE vendor_id=?');
    $stmt->execute([(int)$_GET['id']]);
    $editVendor = $stmt->fetch() ?: null;
}
?>
<section class="panel">
    <h2><?= $editVendor ? 'Edit Vendor' : 'Add Vendor' ?></h2>
    <form method="POST" action="index.php?page=<?= $editVendor ? 'vendor_update' : 'vendor_create' ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($editVendor): ?>
        <input type="hidden" name="vendor_id" value="<?= (int)$editVendor['vendor_id'] ?>">
        <?php endif; ?>
        <label>Vendor Name</label>
        <input name="name" required value="<?= e($editVendor['name'] ?? '') ?>">
        <label>Service Type</label>
        <input name="service_type" required placeholder="Cake, Decoration, Venue" value="<?= e($editVendor['service_type'] ?? '') ?>">
        <label>Price</label>
        <input type="number" name="price" min="0" step="0.01" required value="<?= e((string)($editVendor['price'] ?? '')) ?>">
        <label>Contact Email</label>
        <input type="email" name="contact_email" required value="<?= e($editVendor['contact_email'] ?? '') ?>">
        <button class="btn">Save Vendor</button>
    </form>
</section>

<?php elseif ($page === 'bookings'): ?>

<section class="panel">
    <h2>My Bookings</h2>
    <?php
    $stmt = $pdo->prepare(
        'SELECT b.*, p.child_name, v.name AS vendor_name
         FROM bookings b
         INNER JOIN parties p ON b.party_id=p.party_id
         INNER JOIN vendors v ON b.vendor_id=v.vendor_id
         WHERE p.parent_id=? ORDER BY b.booking_id DESC'
    );
    $stmt->execute([current_user_id()]);
    $bookings = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT party_id, child_name FROM parties WHERE parent_id=?');
    $stmt->execute([current_user_id()]);
    $myParties = $stmt->fetchAll();

    $allVendors = $pdo->query('SELECT vendor_id, name, price FROM vendors ORDER BY name')->fetchAll();
    ?>
    <h3>Create Booking</h3>
    <form method="POST" action="index.php?page=booking_create">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Choose Party</label>
        <select name="party_id" required>
            <option value="">Select party</option>
            <?php foreach ($myParties as $p): ?>
            <option value="<?= (int)$p['party_id'] ?>"><?= e($p['child_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Choose Vendor</label>
        <select name="vendor_id" required>
            <option value="">Select vendor</option>
            <?php foreach ($allVendors as $v): ?>
            <option value="<?= (int)$v['vendor_id'] ?>"><?= e($v['name']) ?> - <?= e((string)$v['price']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn">Book Vendor</button>
    </form>

    <h3>Booking List</h3>
    <div class="table-wrap">
    <table>
        <tr><th>Party</th><th>Vendor</th><th>Amount</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?= e($booking['child_name']) ?></td>
            <td><?= e($booking['vendor_name']) ?></td>
            <td><?= e((string)$booking['amount']) ?></td>
            <td><?= e($booking['status']) ?></td>
            <td>
                <form method="POST" action="index.php?page=booking_delete">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="booking_id" value="<?= (int)$booking['booking_id'] ?>">
                    <button class="danger" onclick="return confirm('Delete this booking?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>