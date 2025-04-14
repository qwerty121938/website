#include <iostream>
using namespace std;

void hanoi(int n, int start, int temp, int target) {
    if (n > 0) {
        hanoi(n - 1, start, target, temp);
        cout << "Ring " << n << " from " << start << " to " << target << endl;
        hanoi(n - 1, temp, start, target);
    }
}

int main() {
    int n;
    cin >> n;
    hanoi(n, 1, 2, 3);
    return 0;
}
