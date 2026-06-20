# Git Publish

Codex cannot write `.git` metadata in this sandbox, so run the helper from normal CMD:

```bat
cd "C:\Users\ceyll\Documents\cpad final\Utm-project"
tools\git_publish_arcade.bat
```

The helper:

```text
initializes Git inside Utm-project only
sets origin to https://github.com/Zoferjenes/Utm-project.git
stages the project files
commits "Build Arcade FixIt MVP"
pulls remote main if it already exists
pushes main to GitHub
```

If Git asks for identity:

```bat
git config --global user.name "exant9"
git config --global user.email "fcfgts@gmail.com"
```
