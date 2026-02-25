<?php
declare(strict_types=1);

session_start();

$config = require __DIR__ . '/config.php';
if (!is_array($config)) {
  $config = [];
}

$contactConfig = is_array($config['contact'] ?? null) ? $config['contact'] : [];
$mailConfig = is_array($config['mail'] ?? null) ? $config['mail'] : [];

$recipientEmail = (string)($contactConfig['to_email'] ?? '');
$publicEmail = (string)($contactConfig['public_email'] ?? $recipientEmail);
$fromEmail = (string)($contactConfig['from_email'] ?? 'noreply@localhost');
$fromName = (string)($contactConfig['from_name'] ?? 'Portfolio Contact');
$subjectPrefix = (string)($contactConfig['subject_prefix'] ?? '[Portfolio]');

$smtpHost = trim((string)($mailConfig['smtp_host'] ?? ''));
$smtpPort = (int)($mailConfig['smtp_port'] ?? 0);

if ($smtpHost !== '') {
  ini_set('SMTP', $smtpHost);
}

if ($smtpPort > 0) {
  ini_set('smtp_port', (string)$smtpPort);
}

ini_set('sendmail_from', $fromEmail);

$formData = [
  'name' => '',
  'email' => '',
  'message' => '',
];

$formState = [
  'type' => '',
  'message' => '',
];

if (empty($_SESSION['contact_csrf'])) {
  $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}

$csrfToken = (string)$_SESSION['contact_csrf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postedToken = (string)($_POST['csrf_token'] ?? '');
  $honeypot = trim((string)($_POST['website'] ?? ''));

  $formData['name'] = trim((string)($_POST['name'] ?? ''));
  $formData['email'] = trim((string)($_POST['email'] ?? ''));
  $formData['message'] = trim((string)($_POST['message'] ?? ''));

  if (!hash_equals($csrfToken, $postedToken)) {
    $formState['type'] = 'error';
    $formState['message'] = 'Sessione non valida. Ricarica la pagina e riprova.';
  } elseif ($honeypot !== '') {
    $formState['type'] = 'error';
    $formState['message'] = 'Invio bloccato dal controllo antispam.';
  } elseif ($formData['name'] === '' || $formData['email'] === '' || $formData['message'] === '') {
    $formState['type'] = 'error';
    $formState['message'] = 'Compila tutti i campi richiesti.';
  } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
    $formState['type'] = 'error';
    $formState['message'] = 'Inserisci una email valida.';
  } elseif (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    $formState['type'] = 'error';
    $formState['message'] = 'Configurazione email non valida in config.php.';
  } else {
    $subject = trim($subjectPrefix . ' Nuovo messaggio da ' . $formData['name']);
    $sentAt = date('Y-m-d H:i:s');
    $senderIp = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    $messageBody = "Nuovo messaggio dal portfolio\n\n";
    $messageBody .= "Nome: {$formData['name']}\n";
    $messageBody .= "Email: {$formData['email']}\n";
    $messageBody .= "Data: {$sentAt}\n";
    $messageBody .= "IP: {$senderIp}\n\n";
    $messageBody .= "Messaggio:\n{$formData['message']}\n";

    $headers = [
      'MIME-Version: 1.0',
      'Content-Type: text/plain; charset=UTF-8',
      'From: ' . $fromName . ' <' . $fromEmail . '>',
      'Reply-To: ' . $formData['name'] . ' <' . $formData['email'] . '>',
      'X-Mailer: PHP/' . phpversion(),
    ];

    $mailSent = @mail($recipientEmail, $subject, $messageBody, implode("\r\n", $headers));

    if ($mailSent) {
      $formState['type'] = 'success';
      $formState['message'] = 'Email inviata con successo. Ti rispondo appena possibile.';
      $formData = ['name' => '', 'email' => '', 'message' => ''];
      $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
      $csrfToken = (string)$_SESSION['contact_csrf'];
    } else {
      $logLine = "[{$sentAt}] {$formData['name']} <{$formData['email']}>\n{$formData['message']}\n----\n";
      @file_put_contents(__DIR__ . '/contact-messages.log', $logLine, FILE_APPEND | LOCK_EX);
      $formState['type'] = 'error';
      $formState['message'] = 'Invio email non riuscito. Messaggio salvato in locale.';
    }
  }
}

function escapeHtml(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Portfolio notturno professionale di Pasquale Magro: developer, gestione database, fotografo dronista e pianista.">
  <title>Pasquale Magro | Night Portfolio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="bg-layer" aria-hidden="true"></div>
  <div class="pointer-glow" id="pointerGlow" aria-hidden="true"></div>

  <header class="site-header" id="home">
    <a class="brand" href="#home"><span class="brand-mark">PM</span> Pasquale Magro</a>
    <button id="navToggle" class="nav-toggle" aria-expanded="false" aria-controls="siteNav">Menu</button>
    <nav id="siteNav" class="site-nav">
      <a href="#about">Profilo</a>
      <a href="#skills">Competenze</a>
      <a href="#projects">Progetti</a>
      <a href="#contact">Contatti</a>
      <a href="portfolio-fotografico.php">Portfolio Foto</a>
    </nav>
  </header>

  <main>
    <section class="section hero reveal">
      <div class="hero-copy">
        <p class="eyebrow">Night Portfolio</p>
        <h1>Pasquale Magro</h1>
        <p class="hero-rotator"><span id="roleRotator">Fotografo (hobbista) + Dronista</span></p>
        <p class="section-lead">Profilo multidisciplinare: sviluppo software, gestione database, fotografia e pianoforte. Precisione tecnica e cura creativa nello stesso flusso di lavoro.</p>
        <div class="hero-cta">
          <a class="btn btn-primary" href="#skills">Vedi competenze</a>
          <a class="btn btn-ghost" href="portfolio-fotografico.php">Portfolio fotografico</a>
        </div>
        <ul class="quick-facts" aria-label="Aree principali">
          <li><span>Fotografo + Dronista</span>Scatti e riprese aeree in ambito hobbistico.</li>
          <li><span>Developer</span>DiscordJS, HTML, PHP, MySQL, JavaScript.</li>
          <li><span>Gestione Database</span>Schema, query e manutenzione operativa.</li>
          <li><span>Pianista</span>Disciplina, ritmo e attenzione al dettaglio.</li>
        </ul>
      </div>

      <aside class="hero-panel panel tilt-card">
        <p class="panel-label">Sintesi professionale</p>
        <p class="status"><span class="status-dot" aria-hidden="true"></span> Disponibile per collaborazioni</p>
        <dl>
          <div>
            <dt>Stack tecnico</dt>
            <dd>DiscordJS, HTML, PHP, MySQL, JavaScript</dd>
          </div>
          <div>
            <dt>Ambiti principali</dt>
            <dd>Bot Discord, sviluppo web, sistemi data-oriented</dd>
          </div>
          <div>
            <dt>Plus creativo</dt>
            <dd>Fotografia hobbistica, drone e pianoforte</dd>
          </div>
        </dl>
      </aside>
    </section>

    <section id="about" class="section reveal">
      <p class="eyebrow">Profilo</p>
      <h2>Chi sono</h2>
      <p class="section-lead">Lavoro con approccio pratico: obiettivi chiari, codice pulito, database affidabili e UX essenziale. La componente creativa migliora estetica, ritmo e comunicazione del progetto.</p>
      <div class="about-grid">
        <article class="panel tilt-card">
          <h3>Mentalita tecnica</h3>
          <p>Sviluppo orientato a stabilita, manutenzione e chiarezza architetturale.</p>
        </article>
        <article class="panel tilt-card">
          <h3>Gestione database</h3>
          <p>MySQL progettato per flussi consistenti e query operative efficienti.</p>
        </article>
        <article class="panel tilt-card">
          <h3>Creative discipline</h3>
          <p>Foto, drone e pianoforte affinano composizione, dettaglio e coerenza.</p>
        </article>
      </div>
    </section>

    <section id="skills" class="section reveal">
      <p class="eyebrow">Competenze</p>
      <h2>Cosa faccio</h2>
      <div class="cards-grid">
        <article class="service-card tilt-card">
          <h3>Sviluppo Bot Discord</h3>
          <p>Bot custom con DiscordJS per moderazione, utility e automazioni su misura.</p>
          <ul>
            <li>Command handling e workflow server</li>
            <li>Utility personalizzate</li>
            <li>Logica dedicata per community</li>
          </ul>
        </article>
        <article class="service-card tilt-card">
          <h3>Sviluppo Web</h3>
          <p>Progetti in HTML, PHP e JavaScript con layout responsivo e codice ordinato.</p>
          <ul>
            <li>Interfacce professionali</li>
            <li>Performance e leggibilita</li>
            <li>Manutenibilita nel tempo</li>
          </ul>
        </article>
        <article class="service-card tilt-card">
          <h3>Gestione Database</h3>
          <p>Strutture MySQL affidabili per supportare applicazioni e dashboard operative.</p>
          <ul>
            <li>Schema e relazioni</li>
            <li>Query e controllo dati</li>
            <li>Ottimizzazione flussi</li>
          </ul>
        </article>
        <article class="service-card service-card-featured tilt-card">
          <p class="card-kicker">Creative Layer</p>
          <h3>Fotografia, Drone e Piano</h3>
          <p>Questa area non e decorativa: porta qualita visiva, sensibilita narrativa e precisione esecutiva anche nei progetti digitali.</p>
          <div class="chip-row">
            <span class="chip">Foto hobbistica</span>
            <span class="chip">Riprese drone</span>
            <span class="chip">Pianoforte</span>
          </div>
          <a class="inline-link" href="portfolio-fotografico.php">Apri il portfolio fotografico</a>
        </article>
      </div>
    </section>

    <section id="projects" class="section reveal">
      <p class="eyebrow">Progetti</p>
      <h2>Focus progettuale</h2>
      <div class="projects-grid">
        <article class="project-card tilt-card">
          <p class="project-meta">Discord</p>
          <h3>Community Bot Suite</h3>
          <p>Automazioni, moderazione e utility operative per community e server privati.</p>
          <p><strong>Stack:</strong> DiscordJS, JavaScript</p>
        </article>
        <article class="project-card tilt-card">
          <p class="project-meta">Web</p>
          <h3>Siti e Portfolio professionali</h3>
          <p>Sviluppo di pagine chiare, veloci e ottimizzate per presentazione professionale.</p>
          <p><strong>Stack:</strong> HTML, CSS, JavaScript, PHP</p>
        </article>
        <article class="project-card tilt-card">
          <p class="project-meta">Data</p>
          <h3>Architetture MySQL</h3>
          <p>Strutture dati solide per gestione informazioni, report e workflow applicativi.</p>
          <p><strong>Stack:</strong> MySQL, PHP</p>
        </article>
      </div>
    </section>

    <section id="contact" class="section reveal">
      <p class="eyebrow">Contatti</p>
      <h2>Parliamo del progetto</h2>
      <p class="section-lead">Compila il form: il messaggio viene inviato via email dal sito.</p>

      <div class="contact-layout">
        <article class="panel contact-panel">
          <h3>Disponibilita</h3>
          <ul class="contact-points">
            <li>Collaborazioni freelance</li>
            <li>Sviluppo bot Discord e web app</li>
            <li>Gestione database MySQL</li>
          </ul>
          <a class="contact-link" href="mailto:<?= escapeHtml($publicEmail) ?>"><?= escapeHtml($publicEmail) ?></a>
          <a class="contact-link" href="portfolio-fotografico.php">Vai al portfolio fotografico</a>
        </article>

        <form id="contactForm" class="contact-form" method="post" action="#contact" novalidate>
          <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
          <div class="hp-field" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
          </div>

          <label>
            Nome
            <input type="text" name="name" required maxlength="120" value="<?= escapeHtml($formData['name']) ?>" placeholder="Il tuo nome">
          </label>
          <label>
            Email
            <input type="email" name="email" required maxlength="160" value="<?= escapeHtml($formData['email']) ?>" placeholder="nome@email.com">
          </label>
          <label>
            Messaggio
            <textarea name="message" rows="5" required maxlength="3000" placeholder="Scrivi qui il tuo progetto"><?= escapeHtml($formData['message']) ?></textarea>
          </label>
          <button type="submit" class="btn btn-primary">Invia richiesta</button>
          <p id="formNotice" class="form-notice<?= $formState['type'] !== '' ? ' ' . escapeHtml($formState['type']) : '' ?>" role="status" aria-live="polite"><?= escapeHtml($formState['message']) ?></p>
        </form>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <p>&copy; <span id="currentYear"></span> Pasquale Magro</p>
    <a href="#home">Torna in alto</a>
  </footer>

  <script src="script.js"></script>
</body>
</html>
