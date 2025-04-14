# my website
#include <bits/stdc++.h>

using namespace std;

long long dp[10000][2]; 
bool visit[10000];      

long long f(int num) {
    if (visit[num]) {
        return dp[num][0]; 
    }
    visit[num] = true;
    if (num == 0) {
        dp[num][0] = 0;
        dp[num][1] = 1; 
        return dp[num][0];
    }
    if (num == 1) {
        dp[num][0] = 1;
        dp[num][1] = 1; 
        return dp[num][0];
    }
    dp[num][0] = f(num - 1) + f(num - 2);
    dp[num][1] = 1 + dp[num - 1][1] + dp[num - 2][1];
    return dp[num][0];
}

int main() {
    int s;
    cin >> s;
    memset(dp, 0, sizeof(dp));
    memset(visit, false, sizeof(visit));
    cout << f(s) << " " << dp[s][1] << endl;
    return 0;
}
