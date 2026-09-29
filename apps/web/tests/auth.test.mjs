import { test, afterEach } from 'node:test';
import assert from 'node:assert/strict';
import { ApiError, apiRequest } from '../src/lib/api/client.ts';
import { SESSION_KEY, YACHT_KEY, parseSession, saveSession, readSession, clearSession, readSelectedYacht, saveSelectedYacht, clearSelectedYacht } from '../src/lib/auth/session.ts';

const originalFetch = globalThis.fetch;
afterEach(() => { globalThis.fetch = originalFetch; delete globalThis.window; });
const session = () => ({ access_token: '1|test-token', expires_at: new Date(Date.now() + 60000).toISOString(), user: { id: 'user', first_name: 'Demo', last_name: 'Captain', email: 'test@example.com' } });
function storage() {
  const values = new Map();
  return { getItem: key => values.get(key) ?? null, setItem: (key, value) => values.set(key, value), removeItem: key => values.delete(key) };
}

test('corrupt, incomplete and expired sessions are rejected', () => {
  for (const raw of [null, '{', '{}', JSON.stringify({ ...session(), expires_at: 'invalid' }), JSON.stringify({ ...session(), expires_at: '2000-01-01' }), JSON.stringify({ ...session(), user: {} })]) assert.equal(parseSession(raw), null);
  assert.deepEqual(parseSession(JSON.stringify(session()))?.user, session().user);
});

test('remember choice persists only minimal session in the selected storage, logout clears both', () => {
  globalThis.window = { localStorage: storage(), sessionStorage: storage() };
  const saved = session();
  saveSession(saved, true);
  assert.deepEqual(readSession(), saved);
  assert.equal(window.sessionStorage.getItem(SESSION_KEY), null);
  saveSession(saved, false);
  assert.equal(window.localStorage.getItem(SESSION_KEY), null);
  assert.deepEqual(readSession(), saved);
  clearSession();
  assert.equal(readSession(), null);
});

test('selected yacht persistence stores only a UUID and clears inaccessible selection', () => {
  globalThis.window = { localStorage: storage(), sessionStorage: storage() };
  const id = 'a7de0000-0000-4000-8000-000000000001';
  saveSelectedYacht(id);
  assert.equal(window.localStorage.getItem(YACHT_KEY), id);
  assert.equal(window.sessionStorage.getItem(YACHT_KEY), null);
  assert.equal(readSelectedYacht(), id);
  window.localStorage.setItem(YACHT_KEY, 'bad');
  assert.equal(readSelectedYacht(), null);
  clearSelectedYacht();
  assert.equal(window.localStorage.getItem(YACHT_KEY), null);
});

test('unavailable browser storage does not break local logout', () => {
  globalThis.window = { get localStorage() { throw new Error('blocked'); }, get sessionStorage() { throw new Error('blocked'); } };
  assert.doesNotThrow(clearSession);
  assert.equal(readSession(), null);
  assert.throws(() => saveSession(session(), true));
});

test('a blocked storage does not hide a valid session in the other storage', () => {
  for (const available of ['localStorage', 'sessionStorage']) {
    const saved = session();
    const working = storage();
    working.setItem(SESSION_KEY, JSON.stringify(saved));
    globalThis.window = {};
    Object.defineProperty(window, available, { value: working });
    Object.defineProperty(window, available === 'localStorage' ? 'sessionStorage' : 'localStorage', {
      get() { throw new Error('blocked'); },
    });
    assert.deepEqual(readSession(), saved);
  }
});

test('API sends bearer, JSON and no cookies; handles empty logout response', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000/';
  globalThis.fetch = async (url, init) => {
    assert.equal(url, 'http://localhost:8000/api/auth/logout');
    assert.equal(init.headers.Authorization, 'Bearer 1|test');
    assert.equal(init.credentials, 'omit');
    assert.equal(init.cache, 'no-store');
    assert.equal(init.method, 'POST');
    return new Response(null, { status: 204 });
  };
  assert.equal(await apiRequest('/auth/logout', { method: 'POST', token: '1|test' }), undefined);
});

test('API sends an explicit yacht UUID only when provided', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000';
  globalThis.fetch = async (_url, init) => {
    assert.equal(init.headers['X-Yacht-Id'], 'a7de0000-0000-4000-8000-000000000001');
    return Response.json({ data: [] });
  };
  await apiRequest('/yacht', { token: '1|test', yachtId: 'a7de0000-0000-4000-8000-000000000001' });
});

test('active yacht header accompanies authenticated feature requests', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000';
  globalThis.fetch = async (url, init) => {
    assert.equal(url, 'http://localhost:8000/api/crew');
    assert.equal(init.headers.Authorization, 'Bearer 1|test');
    assert.equal(init.headers['X-Yacht-Id'], 'a7de0000-0000-4000-8000-000000000001');
    assert.equal(init.cache, 'no-store');
    return Response.json({ data: [] });
  };
  await apiRequest('/crew', { token: '1|test', yachtId: 'a7de0000-0000-4000-8000-000000000001' });
});

test('Crew create and edit use POST and PATCH with the active yacht header', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000';
  for (const [method, url] of [['POST', 'http://localhost:8000/api/crew'], ['PATCH', 'http://localhost:8000/api/crew/member-id']]) {
    globalThis.fetch = async (actualUrl, init) => {
      assert.equal(actualUrl, url);
      assert.equal(init.method, method);
      assert.equal(init.headers['X-Yacht-Id'], 'active-yacht');
      assert.deepEqual(JSON.parse(init.body), { first_name: 'A', last_name: 'B' });
      return Response.json({ data: { id: 'member-id' } });
    };
    await apiRequest(method === 'POST' ? '/crew' : '/crew/member-id', {
      method, token: '1|test', yachtId: 'active-yacht', body: { first_name: 'A', last_name: 'B' },
    });
  }
});

test('Maintenance create, update and completion preserve payloads and active yacht context', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000';
  const calls = [];
  globalThis.fetch = async (url, init) => {
    calls.push({ url, init });
    return Response.json({ data: { id: 'task-id' } });
  };
  await apiRequest('/maintenance', {
    method: 'POST', token: '1|test', yachtId: 'active-yacht',
    body: { title: 'Inspect rigging', type: 'inspection', priority: 'high', due_date: '2026-10-01', assignee_ids: ['crew-id'] },
  });
  await apiRequest('/maintenance/task-id', {
    method: 'PATCH', token: '1|test', yachtId: 'active-yacht', body: { status: 'in_progress' },
  });
  await apiRequest('/maintenance/task-id/complete', {
    method: 'POST', token: '1|test', yachtId: 'active-yacht',
  });

  assert.deepEqual(calls.map(({ url, init }) => [url, init.method, init.headers['X-Yacht-Id']]), [
    ['http://localhost:8000/api/maintenance', 'POST', 'active-yacht'],
    ['http://localhost:8000/api/maintenance/task-id', 'PATCH', 'active-yacht'],
    ['http://localhost:8000/api/maintenance/task-id/complete', 'POST', 'active-yacht'],
  ]);
  assert.deepEqual(JSON.parse(calls[0].init.body), {
    title: 'Inspect rigging', type: 'inspection', priority: 'high', due_date: '2026-10-01', assignee_ids: ['crew-id'],
  });
  assert.deepEqual(JSON.parse(calls[1].init.body), { status: 'in_progress' });
  assert.equal(calls[2].init.body, undefined);
});

test('validation errors remain structured and server internals are not surfaced', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000';
  globalThis.fetch = async () => Response.json({ message: 'internal detail', errors: { email: ['Required'] } }, { status: 422 });
  await assert.rejects(apiRequest('/auth/login'), error => error instanceof ApiError && error.status === 422 && error.errors.email[0] === 'Required' && !error.message.includes('internal'));
  for (const status of [401, 403, 404, 429, 500]) {
    globalThis.fetch = async () => new Response('internal detail', { status });
    await assert.rejects(apiRequest('/tenant'), error => error.status === status && !error.message.includes('internal'));
  }
});

test('network failure is normalized', async () => {
  globalThis.fetch = async () => { throw new Error('Network'); };
  await assert.rejects(apiRequest('/tenant'), error => error instanceof ApiError && error.status === 0);
});

test('login sends credentials only and safely handles malformed error envelopes', async () => {
  process.env.NEXT_PUBLIC_API_URL = 'http://localhost:8000';
  globalThis.fetch = async (url, init) => {
    assert.equal(url, 'http://localhost:8000/api/auth/login');
    assert.equal(init.headers['Content-Type'], 'application/json');
    assert.equal(init.headers.Authorization, undefined);
    assert.deepEqual(JSON.parse(init.body), { email: 'test@example.com', password: 'test' });
    return Response.json({ data: session() });
  };
  const response = await apiRequest('/auth/login', { method: 'POST', body: { email: 'test@example.com', password: 'test' } });
  assert.equal(response.data.access_token, session().access_token);
  for (const payload of [null, { errors: null }, { errors: { email: 'invalid', password: [42] } }]) {
    globalThis.fetch = async () => Response.json(payload, { status: 422 });
    await assert.rejects(apiRequest('/auth/login'), error => error instanceof ApiError && Object.keys(error.errors).length === 0);
  }
});
