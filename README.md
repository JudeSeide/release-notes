# Release notes
Simple cli tool to generate release notes from Jira resolved tasks.

### Installation

```bash
composer install
```

### Usage

```bash
./generate.php release:notes [options]
```

### Options

```bash
  -w, --weeks=WEEKS        Weeks since last notes
  -d, --date=DATE          Date of last notes
  -u, --username=USERNAME  API username
  -t, --token=TOKEN        API password
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
