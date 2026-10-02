#!/usr/bin/env node

import {
  createPrivateKey,
  createPublicKey,
  generateKeyPairSync,
} from 'node:crypto';
import {
  chmodSync,
  closeSync,
  constants,
  fsyncSync,
  lstatSync,
  mkdirSync,
  openSync,
  readFileSync,
  realpathSync,
  writeFileSync,
} from 'node:fs';
import { dirname, isAbsolute, join, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const options = parseArguments(process.argv.slice(2));
if (options.help) {
  usage(process.stdout);
  process.exit(0);
}

try {
  const keyDir = absoluteKeyPath(options.keyDir);
  const repositoryRoot = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
  process.chdir(repositoryRoot);
  assertRepositoryMatches(options.repo);
  assertKeyDirectoryOutsideRepository(keyDir);
  if (options.generate) {
    generateKeys(keyDir);
  }

  const repository = ghJson(['api', `repos/${options.repo}`]);
  const defaultBranch = repository.default_branch;
  if (typeof defaultBranch !== 'string' || defaultBranch === '') {
    throw new Error('Cannot determine the repository default branch.');
  }
  const state = protectionState(options.repo, defaultBranch);
  if (!options.apply) {
    process.stdout.write(`${JSON.stringify({
      mode: 'dry-run',
      repository: options.repo,
      default_branch: defaultBranch,
      release_tag_ruleset: state.tagRuleset,
      release_signing_environment: state.releaseSigning,
      release_renewal_environment: state.releaseRenewal,
      keys_present: keysExist(keyDir),
    })}\n`);
    if (!state.tagRuleset || !state.releaseSigning || !state.releaseRenewal) {
      process.stderr.write('GitHub release protections are incomplete. Re-run with --apply after reviewing this plan.\n');
      process.exit(2);
    }
    process.exit(0);
  }

  if (!keysExist(keyDir)) {
    generateKeys(keyDir);
  }
  validateKeys(keyDir);
  assertNoExistingSecrets(options.repo);
  assertVariablesSafeToSet(options.repo, keyDir);
  applyProtections(options.repo, defaultBranch, state);
  const verified = protectionState(options.repo, defaultBranch);
  if (!verified.tagRuleset || !verified.releaseSigning || !verified.releaseRenewal) {
    throw new Error('GitHub protections could not be verified; no secrets were uploaded.');
  }
  setVariable(options.repo, 'UPDATER_PUBLIC_KEY', readRequired(join(keyDir, 'release.pub')));
  setVariable(options.repo, 'UPDATER_RENEWAL_PUBLIC_KEY', readRequired(join(keyDir, 'renewal.pub')));
  setSecret(options.repo, 'release-signing', 'UPDATER_SIGNING_KEY', readRequired(join(keyDir, 'release.key')));
  setSecret(options.repo, 'release-signing', 'UPDATER_RENEWAL_SIGNING_KEY', readRequired(join(keyDir, 'renewal.key')));
  setSecret(options.repo, 'release-renewal', 'UPDATER_RENEWAL_SIGNING_KEY', readRequired(join(keyDir, 'renewal.key')));
  process.stdout.write('GitHub release signing setup completed without exposing private keys.\n');
} catch (error) {
  process.stderr.write(`${error instanceof Error ? error.message : 'Release signing setup failed.'}\n`);
  process.exit(1);
}

function usage(stream) {
  stream.write('Usage: sh updater/tools/setup-release-signing.sh [--apply] [--repo OWNER/REPO] [--key-dir PATH]\n');
  stream.write('Default: check only. --apply generates missing keys and configures GitHub.\n');
}

function parseArguments(args) {
  const result = {
    repo: 'portaldots/PortalDots',
    keyDir: null,
    apply: false,
    generate: false,
    help: false,
  };
  for (let index = 0; index < args.length; index += 1) {
    const argument = args[index];
    if (argument === '--apply') {
      result.apply = true;
    } else if (argument === '--generate') {
      result.generate = true;
    } else if (argument === '--help' || argument === '-h') {
      result.help = true;
    } else if (argument === '--repo' || argument === '--key-dir') {
      const value = args[index + 1];
      if (typeof value !== 'string' || value === '') {
        usage(process.stderr);
        process.exit(64);
      }
      result[argument === '--repo' ? 'repo' : 'keyDir'] = value;
      index += 1;
    } else {
      usage(process.stderr);
      process.exit(64);
    }
  }
  if (result.keyDir === null) {
    if (typeof process.env.HOME !== 'string' || process.env.HOME === '') {
      process.stderr.write('HOME is required when --key-dir is not specified.\n');
      process.exit(64);
    }
    result.keyDir = join(process.env.HOME, '.portaldots-release-keys');
  }
  if (!/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/.test(result.repo) || result.keyDir === '') {
    usage(process.stderr);
    process.exit(64);
  }
  return result;
}

function assertRepositoryMatches(repo) {
  const remote = runProcess('git', ['remote', 'get-url', 'origin']).stdout.trim();
  const patterns = [
    /^https:\/\/github\.com\/([A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+?)(?:\.git)?\/?$/i,
    /^git@github\.com:([A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+?)(?:\.git)?$/i,
    /^ssh:\/\/git@github\.com\/([A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+?)(?:\.git)?\/?$/i,
  ];
  const match = patterns.map((pattern) => remote.match(pattern)).find(Boolean);
  if (!match || match[1].toLowerCase() !== repo.toLowerCase()) {
    throw new Error('--repo does not match the current checkout origin.');
  }
}

function absoluteKeyPath(path) {
  const absolute = isAbsolute(path) ? path : resolve(process.cwd(), path);
  let parent;
  try {
    parent = realpathSync(dirname(absolute));
  } catch {
    throw new Error('The signing key parent directory must already exist.');
  }
  return join(parent, absolute.split('/').at(-1));
}

function assertKeyDirectoryOutsideRepository(keyDir) {
  const repository = realpathSync(runProcess('git', ['rev-parse', '--show-toplevel']).stdout.trim());
  const metadata = lstatIfExists(keyDir);
  if (metadata?.isSymbolicLink()) {
    throw new Error('Signing keys must be stored outside the Git checkout.');
  }
  const candidate = metadata ? realpathSync(keyDir) : keyDir;
  if (candidate === repository || candidate.startsWith(`${repository}/`)) {
    throw new Error('Signing keys must be stored outside the Git checkout.');
  }
}

function generateKeys(keyDir) {
  if (lstatIfExists(keyDir)) {
    throw new Error('The signing key directory already exists; refusing to overwrite it.');
  }
  mkdirSync(keyDir, { mode: 0o700 });
  chmodSync(keyDir, 0o700);
  for (const name of ['release', 'renewal']) {
    const { privateKey, publicKey } = generateKeyPairSync('ed25519');
    const privateJwk = privateKey.export({ format: 'jwk' });
    const publicJwk = publicKey.export({ format: 'jwk' });
    const seed = base64UrlDecode(privateJwk.d);
    const publicBytes = base64UrlDecode(publicJwk.x);
    if (seed.length !== 32 || publicBytes.length !== 32) {
      throw new Error('Node.js generated an invalid Ed25519 key pair.');
    }
    writeExclusive(join(keyDir, `${name}.key`), `${Buffer.concat([seed, publicBytes]).toString('base64')}\n`, 0o600);
    writeExclusive(join(keyDir, `${name}.pub`), `${publicBytes.toString('base64')}\n`, 0o644);
  }
}

function writeExclusive(path, contents, mode) {
  const descriptor = openSync(path, constants.O_CREAT | constants.O_EXCL | constants.O_WRONLY, mode);
  try {
    writeFileSync(descriptor, contents, { encoding: 'utf8' });
    fsyncSync(descriptor);
  } finally {
    closeSync(descriptor);
  }
  chmodSync(path, mode);
}

function keysExist(keyDir) {
  return ['release.key', 'release.pub', 'renewal.key', 'renewal.pub']
    .every((file) => lstatIfExists(join(keyDir, file))?.isFile());
}

function validateKeys(keyDir) {
  const directory = lstatSync(keyDir);
  if (!directory.isDirectory() || directory.isSymbolicLink() || (directory.mode & 0o077) !== 0) {
    throw new Error('Signing key directory permissions must be 0700 or stricter.');
  }
  for (const name of ['release', 'renewal']) {
    const secretPath = join(keyDir, `${name}.key`);
    const publicPath = join(keyDir, `${name}.pub`);
    const secretMetadata = lstatSync(secretPath);
    const publicMetadata = lstatSync(publicPath);
    if (!secretMetadata.isFile() || secretMetadata.isSymbolicLink() || (secretMetadata.mode & 0o077) !== 0
      || !publicMetadata.isFile() || publicMetadata.isSymbolicLink()) {
      throw new Error(`${name} signing key files or permissions are unsafe.`);
    }
    const secret = strictBase64(readRequired(secretPath));
    const publicBytes = strictBase64(readRequired(publicPath));
    if (secret.length !== 64 || publicBytes.length !== 32) {
      throw new Error(`${name} signing key pair is invalid.`);
    }
    const seed = secret.subarray(0, 32);
    const storedSecretPublic = secret.subarray(32);
    const derivedPublic = derivePublicFromSeed(seed);
    if (!storedSecretPublic.equals(derivedPublic) || !publicBytes.equals(derivedPublic)) {
      throw new Error(`${name} signing key pair is invalid.`);
    }
  }
}

function derivePublicFromSeed(seed) {
  const prefix = Buffer.from('302e020100300506032b657004220420', 'hex');
  const privateKey = createPrivateKey({ key: Buffer.concat([prefix, seed]), format: 'der', type: 'pkcs8' });
  const publicJwk = createPublicKey(privateKey).export({ format: 'jwk' });
  return base64UrlDecode(publicJwk.x);
}

function strictBase64(value) {
  if (!/^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/.test(value)) {
    throw new Error('Signing key is not canonical base64.');
  }
  const decoded = Buffer.from(value, 'base64');
  if (decoded.toString('base64') !== value) {
    throw new Error('Signing key is not canonical base64.');
  }
  return decoded;
}

function base64UrlDecode(value) {
  if (typeof value !== 'string') {
    throw new Error('Ed25519 JWK is missing key material.');
  }
  return Buffer.from(value, 'base64url');
}

function protectionState(repo, defaultBranch) {
  const rulesets = ghPagedList(`repos/${repo}/rulesets?per_page=100`);
  let tagRuleset = false;
  for (const summary of rulesets) {
    if (!isObject(summary) || summary.name !== 'PortalDots release tags' || !Number.isInteger(summary.id)) {
      continue;
    }
    const details = ghJson(['api', `repos/${repo}/rulesets/${summary.id}`]);
    const types = Array.isArray(details.rules) ? details.rules.map((rule) => rule?.type) : [];
    const includes = details.conditions?.ref_name?.include;
    const excludes = details.conditions?.ref_name?.exclude;
    const bypass = details.bypass_actors;
    const allowedBypass = Array.isArray(bypass) && (bypass.length === 0 || (bypass.length === 1
      && isObject(bypass[0]) && bypass[0].actor_id === 5
      && bypass[0].actor_type === 'RepositoryRole' && bypass[0].bypass_mode === 'always'));
    tagRuleset = details.target === 'tag' && details.enforcement === 'active'
      && arraysEqual(includes, ['refs/tags/v*']) && arraysEqual(excludes, []) && allowedBypass
      && ['creation', 'update', 'deletion', 'non_fast_forward'].every((type) => types.includes(type));
    if (!tagRuleset) {
      throw new Error('Existing PortalDots release tag ruleset is not the required restrictive policy.');
    }
  }
  const environments = ghPagedList(`repos/${repo}/environments?per_page=100`, 'environments');
  const names = environments.filter(isObject).map((environment) => environment.name);
  return {
    tagRuleset,
    releaseSigning: names.includes('release-signing')
      && hasDeploymentPolicy(repo, 'release-signing', 'v*', 'tag'),
    releaseRenewal: names.includes('release-renewal')
      && hasDeploymentPolicy(repo, 'release-renewal', defaultBranch, 'branch'),
  };
}

function hasDeploymentPolicy(repo, environment, name, type) {
  const details = ghJson(['api', `repos/${repo}/environments/${environment}`]);
  const deployment = details.deployment_branch_policy;
  if (!isObject(deployment) || deployment.protected_branches !== false
    || deployment.custom_branch_policies !== true) {
    throw new Error(`Environment ${environment} does not have an exclusive custom deployment policy.`);
  }
  const policies = ghPagedList(
    `repos/${repo}/environments/${environment}/deployment-branch-policies?per_page=100`,
    'branch_policies',
  );
  return policies.length === 1 && isObject(policies[0])
    && policies[0].name === name && policies[0].type === type;
}

function applyProtections(repo, defaultBranch, state) {
  if (!state.tagRuleset) {
    ghJson(['api', '--method', 'POST', `repos/${repo}/rulesets`, '--input', '-'], {
      name: 'PortalDots release tags',
      target: 'tag',
      enforcement: 'active',
      bypass_actors: [{ actor_id: 5, actor_type: 'RepositoryRole', bypass_mode: 'always' }],
      conditions: { ref_name: { include: ['refs/tags/v*'], exclude: [] } },
      rules: ['creation', 'update', 'deletion', 'non_fast_forward'].map((type) => ({ type })),
    });
  }
  if (!state.releaseSigning) {
    createEnvironment(repo, 'release-signing', 'v*', 'tag');
  }
  if (!state.releaseRenewal) {
    createEnvironment(repo, 'release-renewal', defaultBranch, 'branch');
  }
}

function createEnvironment(repo, environment, policy, type) {
  const existing = ghPagedList(`repos/${repo}/environments?per_page=100`, 'environments');
  if (existing.some((entry) => isObject(entry) && entry.name === environment)) {
    throw new Error(`Existing environment ${environment} lacks the required policy; refusing to alter it.`);
  }
  ghJson(['api', '--method', 'PUT', `repos/${repo}/environments/${environment}`, '--input', '-'], {
    deployment_branch_policy: { protected_branches: false, custom_branch_policies: true },
  });
  ghJson(['api', '--method', 'POST',
    `repos/${repo}/environments/${environment}/deployment-branch-policies`, '--input', '-'], {
    name: policy,
    type,
  });
}

function assertNoExistingSecrets(repo) {
  const names = ghPagedList(`repos/${repo}/environments?per_page=100`, 'environments')
    .filter(isObject).map((entry) => entry.name);
  for (const environment of ['release-signing', 'release-renewal']) {
    if (!names.includes(environment)) {
      continue;
    }
    const response = runProcess('gh', ['secret', 'list', '--repo', repo, '--env', environment, '--json', 'name']);
    const secrets = parseJson(response.stdout, `Cannot inspect ${environment} secrets.`);
    if (!Array.isArray(secrets) || secrets.length !== 0) {
      throw new Error(`Existing ${environment} secrets will not be overwritten.`);
    }
  }
}

function assertVariablesSafeToSet(repo, keyDir) {
  const response = runProcess('gh', ['variable', 'list', '--repo', repo, '--json', 'name,value']);
  const variables = parseJson(response.stdout, 'Cannot inspect repository variables.');
  if (!Array.isArray(variables)) {
    throw new Error('Cannot inspect repository variables.');
  }
  const expected = new Map([
    ['UPDATER_PUBLIC_KEY', readRequired(join(keyDir, 'release.pub'))],
    ['UPDATER_RENEWAL_PUBLIC_KEY', readRequired(join(keyDir, 'renewal.pub'))],
  ]);
  for (const variable of variables) {
    if (isObject(variable) && expected.has(variable.name) && expected.get(variable.name) !== variable.value) {
      throw new Error(`Existing variable ${variable.name} has a different value.`);
    }
  }
}

function setVariable(repo, name, value) {
  runProcess('gh', ['variable', 'set', name, '--repo', repo, '--body', value]);
}

function setSecret(repo, environment, name, value) {
  const result = spawnSync('gh', ['secret', 'set', name, '--repo', repo, '--env', environment], {
    input: value,
    encoding: 'utf8',
    maxBuffer: 16 * 1024 * 1024,
    stdio: ['pipe', 'pipe', 'pipe'],
  });
  if (result.error || result.status !== 0) {
    throw new Error(`Cannot set ${environment} secret ${name}.`);
  }
}

function ghJson(argumentsList, input = null) {
  const response = runProcess('gh', argumentsList, input === null ? null : JSON.stringify(input));
  return parseJson(response.stdout === '' ? '{}' : response.stdout, 'GitHub CLI returned malformed JSON.');
}

function ghPagedList(endpoint, container = null) {
  const pages = ghJson(['api', '--paginate', '--slurp', endpoint]);
  if (!Array.isArray(pages)) {
    throw new Error('GitHub CLI pagination response is malformed.');
  }
  const result = [];
  for (const page of pages) {
    const items = container === null ? page : (isObject(page) ? page[container] : null);
    if (!Array.isArray(items)) {
      throw new Error('GitHub CLI pagination page is malformed.');
    }
    result.push(...items);
  }
  return result;
}

function runProcess(command, args, input = null) {
  const result = spawnSync(command, args, {
    input: input ?? '',
    encoding: 'utf8',
    maxBuffer: 16 * 1024 * 1024,
    stdio: ['pipe', 'pipe', 'pipe'],
  });
  if (result.error) {
    throw new Error(`Cannot start required command ${command}: ${result.error.message}`);
  }
  if (result.status !== 0) {
    throw new Error(`${command} failed: ${(result.stderr ?? '').trim()}`);
  }
  return { stdout: result.stdout ?? '', stderr: result.stderr ?? '' };
}

function parseJson(value, message) {
  try {
    return JSON.parse(value);
  } catch {
    throw new Error(message);
  }
}

function readRequired(path) {
  let value;
  try {
    value = readFileSync(path, 'utf8').trim();
  } catch {
    throw new Error('Signing key material is missing.');
  }
  if (value === '') {
    throw new Error('Signing key material is missing.');
  }
  return value;
}

function lstatIfExists(path) {
  try {
    return lstatSync(path);
  } catch (error) {
    if (error?.code === 'ENOENT') {
      return null;
    }
    throw error;
  }
}

function arraysEqual(left, right) {
  return Array.isArray(left) && left.length === right.length
    && left.every((value, index) => value === right[index]);
}

function isObject(value) {
  return value !== null && typeof value === 'object' && !Array.isArray(value);
}
