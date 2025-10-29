FROM python:3.12-slim-trixie AS python-image
COPY --from=ghcr.io/astral-sh/uv:latest /uv /uvx /bin/

# install libmagic
RUN apt-get update && apt-get install -y libmagic-dev

ADD ./vendor/mateffy/llm-magic/python /app

WORKDIR /app
RUN uv sync

## test that the python script works
ADD ./expose.pdf /app/expose.pdf
RUN mkdir -p /app/output
RUN /app/.venv/bin/python prepare-pdf.py /app/output /app/expose.pdf 123
