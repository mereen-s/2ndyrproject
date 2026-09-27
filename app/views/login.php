<div class="med-bg" aria-hidden="true">
  <span>&#9877;</span>   <!-- staff of Aesculapius -->
  <span>&#10010;</span>  <!-- heavy cross -->
  <span>&#9883;</span>   <!-- atom -->
  <span>&#128137;</span> <!-- syringe -->
  <span>&#129658;</span> <!-- test tube -->
  <span>&#10011;</span>  <!-- outlined cross -->
  <span>&#129656;</span> <!-- DNA -->
  <span>&#10010;</span>
</div>

<div class="login-shell">

  <!-- LEFT: login form -->
  <div class="login-page">
    <h2>Log in</h2>
    <form method="post" action="<?= BASE_URL ?>/index.php?page=do_login">
      <?= csrf_field() ?>
      <label>Username</label><input name="username" required>
      <label>Password</label><input name="password" type="password" required>
      <button>Log in</button>
    </form>
    <p class="note">New staff member? <a href="<?= BASE_URL ?>/index.php?page=signup">Create an account</a></p>
    <p class="note">Role-based access: you are routed to your role's dashboard after login.</p>
  </div>

  <!-- RIGHT: branding panel with rotating DNA helix -->
  <aside class="brand-panel" aria-hidden="true">
    <div class="helix-wrap">
      <svg class="helix" viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg">
        <g class="helix-spin">
          <line class="rung warm" x1="100.0" y1="20.0" x2="100.0" y2="20.0" opacity="0.55"/>
          <line class="rung " x1="133.8" y1="31.4" x2="66.2" y2="31.4" opacity="0.55"/>
          <line class="rung " x1="155.9" y1="42.9" x2="44.1" y2="42.9" opacity="0.55"/>
          <line class="rung warm" x1="158.5" y1="54.3" x2="41.5" y2="54.3" opacity="0.55"/>
          <line class="rung " x1="140.8" y1="65.7" x2="59.2" y2="65.7" opacity="0.55"/>
          <line class="rung " x1="108.9" y1="77.1" x2="91.1" y2="77.1" opacity="0.55"/>
          <line class="rung warm" x1="74.0" y1="88.6" x2="126.0" y2="88.6" opacity="0.55"/>
          <line class="rung " x1="48.0" y1="100.0" x2="152.0" y2="100.0" opacity="0.55"/>
          <line class="rung " x1="40.2" y1="111.4" x2="159.8" y2="111.4" opacity="0.55"/>
          <line class="rung warm" x1="53.1" y1="122.9" x2="146.9" y2="122.9" opacity="0.55"/>
          <line class="rung " x1="82.3" y1="134.3" x2="117.7" y2="134.3" opacity="0.55"/>
          <line class="rung " x1="117.7" y1="145.7" x2="82.3" y2="145.7" opacity="0.55"/>
          <line class="rung warm" x1="146.9" y1="157.1" x2="53.1" y2="157.1" opacity="0.55"/>
          <line class="rung " x1="159.8" y1="168.6" x2="40.2" y2="168.6" opacity="0.55"/>
          <line class="rung " x1="152.0" y1="180.0" x2="48.0" y2="180.0" opacity="0.55"/>
          <line class="rung warm" x1="126.0" y1="191.4" x2="74.0" y2="191.4" opacity="0.55"/>
          <line class="rung " x1="91.1" y1="202.9" x2="108.9" y2="202.9" opacity="0.55"/>
          <line class="rung " x1="59.2" y1="214.3" x2="140.8" y2="214.3" opacity="0.55"/>
          <line class="rung warm" x1="41.5" y1="225.7" x2="158.5" y2="225.7" opacity="0.55"/>
          <line class="rung " x1="44.1" y1="237.1" x2="155.9" y2="237.1" opacity="0.55"/>
          <line class="rung " x1="66.2" y1="248.6" x2="133.8" y2="248.6" opacity="0.55"/>
          <line class="rung warm" x1="100.0" y1="260.0" x2="100.0" y2="260.0" opacity="0.55"/>
          <path d="M 100.0 20.0 L 133.8 31.4 L 155.9 42.9 L 158.5 54.3 L 140.8 65.7 L 108.9 77.1 L 74.0 88.6 L 48.0 100.0 L 40.2 111.4 L 53.1 122.9 L 82.3 134.3 L 117.7 145.7 L 146.9 157.1 L 159.8 168.6 L 152.0 180.0 L 126.0 191.4 L 91.1 202.9 L 59.2 214.3 L 41.5 225.7 L 44.1 237.1 L 66.2 248.6 L 100.0 260.0" fill="none" stroke="rgba(234,246,250,.7)" stroke-width="2.5"/>
          <path d="M 100.0 20.0 L 66.2 31.4 L 44.1 42.9 L 41.5 54.3 L 59.2 65.7 L 91.1 77.1 L 126.0 88.6 L 152.0 100.0 L 159.8 111.4 L 146.9 122.9 L 117.7 134.3 L 82.3 145.7 L 53.1 157.1 L 40.2 168.6 L 48.0 180.0 L 74.0 191.4 L 108.9 202.9 L 140.8 214.3 L 158.5 225.7 L 155.9 237.1 L 133.8 248.6 L 100.0 260.0" fill="none" stroke="rgba(143,208,229,.7)" stroke-width="2.5"/>
          <circle class="node-a" cx="100.0" cy="20.0" r="5.5" opacity="1.00"/>
          <circle class="node-a" cx="133.8" cy="31.4" r="5.2" opacity="0.96"/>
          <circle class="node-a" cx="155.9" cy="42.9" r="4.5" opacity="0.84"/>
          <circle class="node-a" cx="158.5" cy="54.3" r="3.7" opacity="0.69"/>
          <circle class="node-a" cx="140.8" cy="65.7" r="2.9" opacity="0.57"/>
          <circle class="node-a" cx="108.9" cy="77.1" r="2.5" opacity="0.50"/>
          <circle class="node-a" cx="74.0" cy="88.6" r="2.6" opacity="0.52"/>
          <circle class="node-a" cx="48.0" cy="100.0" r="3.2" opacity="0.62"/>
          <circle class="node-a" cx="40.2" cy="111.4" r="4.1" opacity="0.77"/>
          <circle class="node-a" cx="53.1" cy="122.9" r="4.9" opacity="0.91"/>
          <circle class="node-a" cx="82.3" cy="134.3" r="5.4" opacity="0.99"/>
          <circle class="node-a" cx="117.7" cy="145.7" r="5.4" opacity="0.99"/>
          <circle class="node-a" cx="146.9" cy="157.1" r="4.9" opacity="0.91"/>
          <circle class="node-a" cx="159.8" cy="168.6" r="4.1" opacity="0.77"/>
          <circle class="node-a" cx="152.0" cy="180.0" r="3.3" opacity="0.63"/>
          <circle class="node-a" cx="126.0" cy="191.4" r="2.6" opacity="0.52"/>
          <circle class="node-a" cx="91.1" cy="202.9" r="2.5" opacity="0.50"/>
          <circle class="node-a" cx="59.2" cy="214.3" r="2.9" opacity="0.57"/>
          <circle class="node-a" cx="41.5" cy="225.7" r="3.7" opacity="0.69"/>
          <circle class="node-a" cx="44.1" cy="237.1" r="4.5" opacity="0.84"/>
          <circle class="node-a" cx="66.2" cy="248.6" r="5.2" opacity="0.96"/>
          <circle class="node-a" cx="100.0" cy="260.0" r="5.5" opacity="1.00"/>
          <circle class="node-b" cx="100.0" cy="20.0" r="2.5" opacity="0.50"/>
          <circle class="node-b" cx="66.2" cy="31.4" r="2.8" opacity="0.54"/>
          <circle class="node-b" cx="44.1" cy="42.9" r="3.5" opacity="0.66"/>
          <circle class="node-b" cx="41.5" cy="54.3" r="4.3" opacity="0.81"/>
          <circle class="node-b" cx="59.2" cy="65.7" r="5.1" opacity="0.93"/>
          <circle class="node-b" cx="91.1" cy="77.1" r="5.5" opacity="1.00"/>
          <circle class="node-b" cx="126.0" cy="88.6" r="5.4" opacity="0.98"/>
          <circle class="node-b" cx="152.0" cy="100.0" r="4.8" opacity="0.88"/>
          <circle class="node-b" cx="159.8" cy="111.4" r="3.9" opacity="0.73"/>
          <circle class="node-b" cx="146.9" cy="122.9" r="3.1" opacity="0.59"/>
          <circle class="node-b" cx="117.7" cy="134.3" r="2.6" opacity="0.51"/>
          <circle class="node-b" cx="82.3" cy="145.7" r="2.6" opacity="0.51"/>
          <circle class="node-b" cx="53.1" cy="157.1" r="3.1" opacity="0.59"/>
          <circle class="node-b" cx="40.2" cy="168.6" r="3.9" opacity="0.73"/>
          <circle class="node-b" cx="48.0" cy="180.0" r="4.7" opacity="0.87"/>
          <circle class="node-b" cx="74.0" cy="191.4" r="5.4" opacity="0.98"/>
          <circle class="node-b" cx="108.9" cy="202.9" r="5.5" opacity="1.00"/>
          <circle class="node-b" cx="140.8" cy="214.3" r="5.1" opacity="0.93"/>
          <circle class="node-b" cx="158.5" cy="225.7" r="4.3" opacity="0.81"/>
          <circle class="node-b" cx="155.9" cy="237.1" r="3.5" opacity="0.66"/>
          <circle class="node-b" cx="133.8" cy="248.6" r="2.8" opacity="0.54"/>
          <circle class="node-b" cx="100.0" cy="260.0" r="2.5" opacity="0.50"/>
        </g>
      </svg>
    </div>
    <h1 class="brand-title">Centralized Clinic, Ward &amp;<br><b>Laboratory Coordination System</b></h1>
    <p class="brand-sub">One connected record across every department &mdash; from first registration to discharge.</p>
    <div class="brand-chips">
      <span>Clinics</span>
      <span>Wards</span>
      <span>Laboratories</span>
    </div>
  </aside>

</div>
