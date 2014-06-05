{% include 'partials/object-nav.volt' %}

<h2>{{ object.title | e }}</h2>
{%  if object.description  %}<p><em>{{ object.description | e }}</em></p>{% endif %}
<p><span class="date" title="{{ object.getDateCreated('j M Y, g:ia') }}">{{ object.getDateCreated('j M Y') }}</span></p>
<hr/>
<pre><code>{{ decrypted_content }}</code></pre>