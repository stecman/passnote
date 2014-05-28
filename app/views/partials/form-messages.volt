{% if form.getMessages() | length %}
	<div class="alert alert-error">
		<p><strong>Whoops, there {% if form.getMessages() | length > 1 %}were some problems{% else %}was a problem{% endif %}:</strong></p>

		{% for message in form.getMessages() %}
			<p>{{ message }}</p>
		{% endfor %}
	</div>
{% endif %}