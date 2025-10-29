FROM mateffy/data-wizard-python AS python-image
FROM mateffy/data-wizard-base AS base


## Application specific part

# Copy the application code
COPY . /app
COPY ./etc/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY .env.docker /app/.env

RUN mkdir -p /app/database
RUN touch /app/database/database.sqlite

COPY ./etc/php.ini /usr/local/etc/php/php.ini

RUN composer install --no-dev --no-interaction --no-progress --no-suggest

RUN php artisan storage:link
RUN php artisan migrate --force
RUN #php artisan filament:cache-components


# Copy the entire Python environment from the first stage
COPY --from=python-image /app /app/vendor/mateffy/llm-magic/python
COPY --from=python-image /app/.venv /app/vendor/mateffy/llm-magic/python/.venv
COPY --from=python-image /app/.venv/bin/python /app/vendor/mateffy/llm-magic/python/.venv/bin/python
COPY --from=python-image /usr/local/lib/python3.12/ /usr/local/lib/python3.12/
COPY --from=python-image /usr/local/lib/libpython3.12.so.1.0 /usr/local/lib/libpython3.12.so.1.0
#ADD ./expose.pdf /app/expose.pdf

# make sure the output directory exists
#RUN mkdir -p /app/output
#
#WORKDIR /app/vendor/mateffy/llm-magic/python
#
## Test that the python script works
#RUN /app/vendor/mateffy/llm-magic/python/.venv/bin/python prepare-pdf.py /app/output /app/expose.pdf 1233

WORKDIR /app

# Hotfix: OpenAI Gemini Issue: https://github.com/openai-php/client/pull/502
COPY ./resources/OpenAiPhpHotfixCreateStreamedResponse.php ./vendor/openai-php/client/src/Responses/Chat/CreateStreamedResponse.php

ENV LLM_MAGIC_PYTHON_USE_UV=false
ENV LLM_MAGIC_PYTHON_CWD=/app/vendor/mateffy/llm-magic/python
ENV LLM_MAGIC_PYTHON_BIN_PATH=/app/vendor/mateffy/llm-magic/python/.venv/bin/python

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
