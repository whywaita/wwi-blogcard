# GitHub App Setup for Release Automation

This document explains how to set up a GitHub App to enable automated releases via the tagpr workflow.

## Why GitHub App?

The tagpr workflow creates releases when PRs are merged. To trigger the `release.yml` workflow from these automated releases, we need to use a GitHub App token instead of the default `GITHUB_TOKEN`.

**Why?** GitHub's `GITHUB_TOKEN` has a security limitation: events created by workflows using `GITHUB_TOKEN` cannot trigger other workflows. This prevents infinite workflow loops but also means our `release.yml` won't run when tagpr publishes a release.

Using a GitHub App token solves this problem while being more secure than Personal Access Tokens (PATs).

## Setup Steps

### 1. Create a GitHub App

1. Go to your organization or account Settings → Developer settings → GitHub Apps
2. Click "New GitHub App"
3. Fill in the required fields:
   - **GitHub App name**: Choose a unique name (e.g., "wwi-blogcard-release-bot")
   - **Homepage URL**: Your repository URL
   - **Webhook**: Uncheck "Active" (not needed for this use case)

### 2. Configure Permissions

Under "Repository permissions", set:
- **Contents**: Read and write (for creating tags and releases)
- **Pull requests**: Read and write (for tagpr to manage PRs)
- **Issues**: Read (for tagpr to read issue labels)

### 3. Generate and Save Private Key

1. After creating the app, scroll down to "Private keys"
2. Click "Generate a private key"
3. Save the downloaded `.pem` file securely

### 4. Install the App

1. Go to "Install App" in the left sidebar
2. Install it on the repository where you want to use it
3. Select "Only select repositories" and choose your repository

### 5. Configure Repository Secrets and Variables

#### Variables (Settings → Secrets and variables → Actions → Variables)
- **Name**: `APP_ID`
- **Value**: Your GitHub App ID (found on the app's settings page)

#### Secrets (Settings → Secrets and variables → Actions → Secrets)
- **Name**: `APP_PRIVATE_KEY`
- **Value**: Contents of the `.pem` file (copy-paste the entire file)

## Verification

After setup, when a tagpr PR is merged:
1. The tagpr workflow will use the GitHub App token
2. A release will be published
3. The `release.yml` workflow will be triggered by the release event
4. Release assets will be built and uploaded

## Security Notes

- GitHub App tokens expire after 1 hour (automatically managed by the action)
- Tokens are automatically revoked after workflow completion
- This is more secure than using long-lived Personal Access Tokens
- The app only has access to repositories where it's installed

## References

- [actions/create-github-app-token](https://github.com/actions/create-github-app-token)
- [Songmu/tagpr documentation](https://github.com/Songmu/tagpr)
- [GitHub Apps documentation](https://docs.github.com/en/apps)
