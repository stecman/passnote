<h3 class="obj-title">Version: {{ object.title | e }}</h3>
{% if object.description  %}<p><em>{{ object.description | e }}</em></p>{% endif %}
<p><span class="date">Version at {{ version.getDateCreated('j M Y, g:ia') }}</span></p>

<div class="row h-clearfix">
	<div class="col-md-6">
	{% if prev_version %}
			<a href="/object/{{ object.id }}/versions/{{ prev_version.id }}">&laquo; {{ prev_version.getDateCreated('j M Y, g:ia') }}</a>
		{% endif %}
	</div>
	<div class="col-md-6 text-right">
		{% if next_version %}
			<a href="/object/{{ object.id }}/versions/{{ next_version.id }}">{{ next_version.getDateCreated('j M Y, g:ia') }} &raquo;</a>
		{% endif %}
	</div>
</div>

<hr/>
<pre><code>{{ decrypted_content }}</code></pre>