{% include 'partials/object-nav.volt' %}

<h2>{{ object.title | e }}</h2>
{%  if object.description  %}<p><em>{{ object.description | e }}</em></p>{% endif %}
<hr/>

{% for version in versions %}
	<h3><span class="date">{{ version.getDateCreated('j M Y, g:ia') }}</span> {% if loop.first %}(current version){% endif %}</h3>
	<pre>{{ version._diff }}</pre>
	{% else %}
	<p>There are no historic versions of this object.</p>
{% endfor %}