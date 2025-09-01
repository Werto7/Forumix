<h1>A Concept of a Forum Software</h1>

<p><strong>Status:</strong></p>
<ul>
  <li><span style="color:green;"><strong>Login function:</strong> working ✅</span></li>
  <li><span style="color:red;"><strong>Register function:</strong> not working ❌</span></li>
</ul>

<p><strong>Requirements:</strong></p>
<ul>
  <li><code>gnupg</code></li>
  <li><code>imagemagick</code></li>
</ul>

<p><strong>Recommended <code>php.ini</code> settings:</strong></p>
<pre><code>session.cookie_httponly = 1
session.cookie_samesite = "Strict"
</code></pre>

<p>The forum software has an integrated registration captcha.

Before the forum is started, the captcha should be prepared. More information about this here:
https://github.com/Werto7/spinCaptcha</p>
